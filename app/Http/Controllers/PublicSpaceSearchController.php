<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Models\Review;
use App\Models\Space;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSpaceSearchController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'event_type_id' => ['nullable', 'integer', 'exists:event_types,id'],
            'min_rate' => ['nullable', 'numeric', 'min:0'],
            'max_rate' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:newest,price_low,price_high,capacity,name,rating'],
            'lang' => ['nullable', 'in:ar,en'],
        ]);

        $search = trim($filters['search'] ?? '');
        $address = trim($filters['address'] ?? '');
        $venueId = $filters['venue_id'] ?? null;
        $capacity = $filters['capacity'] ?? null;
        $eventTypeId = $filters['event_type_id'] ?? null;
        $minRate = $filters['min_rate'] ?? null;
        $maxRate = $filters['max_rate'] ?? null;
        $sort = $filters['sort'] ?? 'newest';
        $isEnglish = ($filters['lang'] ?? 'en') === 'en';
        $searched = $search !== '' || $address !== '' || $venueId !== null || $capacity !== null
            || $eventTypeId !== null || $minRate !== null || $maxRate !== null;

        $averageRating = Review::query()
            ->join('bookings', 'bookings.id', '=', 'reviews.booking_id')
            ->selectRaw('AVG(reviews.rating)')
            ->whereColumn('bookings.venue_id', 'spaces.venue_id');

        $reviewCount = Review::query()
            ->join('bookings', 'bookings.id', '=', 'reviews.booking_id')
            ->selectRaw('COUNT(reviews.id)')
            ->whereColumn('bookings.venue_id', 'spaces.venue_id');

        $spacesQuery = Space::query()
            ->select('spaces.*')
            ->addSelect([
                'venue_average_rating' => $averageRating,
                'venue_review_count' => $reviewCount,
            ])
            ->with([
                'venue',
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
                'spaceLayouts.eventTypes',
            ])
            ->where('spaces.status', 'active')
            ->whereHas('venue', function ($query) use ($address): void {
                $query->where('status', 'approved')
                    ->where('is_active_by_owner', true)
                    ->when($address !== '', fn ($venueQuery) => $venueQuery
                        ->where('address', $address));
            })
            ->when($search !== '', fn ($query) => $query->where(function ($matches) use ($search): void {
                $matches->where('spaces.name', 'like', '%'.$search.'%')
                    ->orWhere('spaces.description', 'like', '%'.$search.'%')
                    ->orWhereHas('venue', fn ($venueQuery) => $venueQuery
                        ->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('spaceLayouts', fn ($layoutQuery) => $layoutQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('eventTypes', fn ($eventTypeQuery) => $eventTypeQuery
                            ->where('name', 'like', '%'.$search.'%')));
            }))
            ->when($search !== '', fn ($query) => $query->where(function ($matches) use ($search): void {
                $matches->where('spaces.name', 'like', '%'.$search.'%')
                    ->orWhere('spaces.description', 'like', '%'.$search.'%')
                    ->orWhereHas('venue', fn ($venueQuery) => $venueQuery
                        ->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('spaceLayouts', fn ($layoutQuery) => $layoutQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('eventTypes', fn ($eventTypeQuery) => $eventTypeQuery
                            ->where('name', 'like', '%'.$search.'%')));
            }))
            ->when($venueId !== null, fn ($query) => $query->where('venue_id', $venueId))
            ->when($capacity !== null, fn ($query) => $query->where('capacity', '>=', $capacity))
            ->when($eventTypeId !== null, fn ($query) => $query->whereHas(
                'spaceLayouts.eventTypes',
                fn ($eventTypeQuery) => $eventTypeQuery->whereKey($eventTypeId)
            ))
            ->when($minRate !== null, fn ($query) => $query->where('hourly_rate', '>=', $minRate))
            ->when($maxRate !== null, fn ($query) => $query->where('hourly_rate', '<=', $maxRate));

        match ($sort) {
            'price_low' => $spacesQuery->orderByRaw('spaces.hourly_rate IS NULL')->orderBy('spaces.hourly_rate'),
            'price_high' => $spacesQuery->orderByDesc('spaces.hourly_rate'),
            'capacity' => $spacesQuery->orderBy('spaces.capacity'),
            'name' => $spacesQuery->orderBy('spaces.name'),
            'rating' => $spacesQuery->orderByDesc('venue_average_rating')->orderByDesc('venue_review_count'),
            default => $spacesQuery->latest('spaces.created_at'),
        };

        $spaces = $spacesQuery
            ->paginate(9)
            ->withQueryString();

        $favoriteSpaceIds = $request->user()?->favoriteSpaces()
            ->whereIn('spaces.id', $spaces->getCollection()->pluck('id'))
            ->pluck('spaces.id')
            ->all() ?? [];

        $venues = Venue::query()
            ->where('status', 'approved')
            ->where('is_active_by_owner', true)
            ->whereHas('spaces', fn ($query) => $query->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'address']);

        $eventTypes = EventType::query()
            ->whereHas('spaceLayouts.space', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('venue', fn ($venueQuery) => $venueQuery
                    ->where('status', 'approved')
                    ->where('is_active_by_owner', true)))
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedVenue = $venueId === null ? null : $venues->firstWhere('id', (int) $venueId);
        $voiceSummary = null;

        if ($searched && $request->boolean('voice') && $spaces->currentPage() === 1) {
            $topSpaces = $spaces->getCollection()->take(3);

            if ($topSpaces->isEmpty()) {
                $voiceSummary = $isEnglish
                    ? 'We found no matching places. Try changing your address or guest count.'
                    : 'ما لقينا مساحات بتناسب طلبك. جرّبي تعدّلي الموقع أو عدد الأشخاص.';
            } else {
                $intro = match ($topSpaces->count()) {
                    1 => $isEnglish ? 'We found one matching place.' : 'لقينا إلك مساحة مناسبة.',
                    2 => $isEnglish ? 'We found two matching places.' : 'لقينا إلك مساحتين مناسبات.',
                    default => $isEnglish ? 'Here are the three closest matches.' : 'هاي أقرب ثلاث مساحات للعدد اللي طلبته.',
                };

                $details = $topSpaces->map(function (Space $space) use ($isEnglish): string {
                    $name = \Illuminate\Support\Str::limit($space->name, 60);
                    $venue = \Illuminate\Support\Str::limit($space->venue->name, 60);

                    return $isEnglish
                        ? "{$name} at {$venue} can accommodate {$space->capacity} guests."
                        : "مساحة {$name} في {$venue}، بتسع {$space->capacity} شخص.";
                })->implode(' ');

                $voiceSummary = $intro.' '.$details;
            }
        }

        $voiceSummaryAudioUrl = $voiceSummary === null
            ? null
            : \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'spaces.search.summary-audio',
                now()->addMinutes(5),
                ['summary' => $voiceSummary],
            );

        return view('public.spaces.search', compact(
            'search',
            'address',
            'capacity',
            'venueId',
            'eventTypeId',
            'minRate',
            'maxRate',
            'sort',
            'searched',
            'spaces',
            'favoriteSpaceIds',
            'venues',
            'eventTypes',
            'selectedVenue',
            'voiceSummary',
            'voiceSummaryAudioUrl',
            'isEnglish',
        ));
    }
}

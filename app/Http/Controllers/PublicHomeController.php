<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Models\Space;
use App\Models\Venue;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function index(): View
    {
        $venues = Venue::query()
            ->where('status', 'approved')
            ->where('is_active_by_owner', true)
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->whereHas('spaces', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('images'))
            ->whereHas('images')
            ->withCount(['spaces as active_spaces_count' => fn ($query) => $query->where('status', 'active')])
            ->with([
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
            ])
            ->orderBy('address')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Venue $venue) => mb_strtolower(trim($venue->address)))
            ->map(function ($venues): array {
                $venue = $venues->first();

                return [
                    'address' => $venue->address,
                    'image_url' => $venue->images->first()->image_url,
                    'description' => $venue->description,
                    'space_count' => $venues->sum('active_spaces_count'),
                ];
            })
            ->take(6)
            ->values();

        $eventTypes = EventType::query()
            ->whereHas('spaceLayouts.space', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('venue', fn ($venueQuery) => $venueQuery
                    ->where('status', 'approved')
                    ->where('is_active_by_owner', true)
                    ->whereHas('images')))
            ->with([
                'spaceLayouts.space' => fn ($query) => $query
                    ->where('status', 'active')
                    ->whereHas('venue', fn ($venueQuery) => $venueQuery
                        ->where('status', 'approved')
                        ->where('is_active_by_owner', true)
                        ->whereHas('images')),
                'spaceLayouts.space.venue.images' => fn ($query) => $query
                    ->orderByDesc('is_primary')
                    ->orderBy('id'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (EventType $eventType): ?array {
                $space = $eventType->spaceLayouts
                    ->pluck('space')
                    ->filter()
                    ->first(fn ($space) => $space->venue && $space->venue->images->isNotEmpty());

                if (! $space) {
                    return null;
                }

                return [
                    'id' => $eventType->id,
                    'name' => $eventType->name,
                    'description' => $eventType->description,
                    'image_url' => $space->venue->images->first()->image_url,
                ];
            })
            ->filter()
            ->values();

        $featuredVenues = Venue::query()
            ->where('status', 'approved')
            ->where('is_active_by_owner', true)
            ->whereHas('spaces', fn ($query) => $query->where('status', 'active'))
            ->whereHas('images')
            ->with([
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
            ])
            ->withCount([
                'spaces as active_spaces_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->latest()
            ->limit(6)
            ->get();

        $latestPlaces = Space::query()
            ->where('status', 'active')
            ->whereHas('venue', fn ($query) => $query
                ->where('status', 'approved')
                ->where('is_active_by_owner', true))
            ->whereHas('images')
            ->with([
                'venue',
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
            ])
            ->latest()
            ->limit(6)
            ->get();

        return view('public.home-navy', [
            'locations' => $venues,
            'featuredVenues' => $featuredVenues,
            'latestPlaces' => $latestPlaces,
            'occasionCards' => $eventTypes,
        ]);
    }
}

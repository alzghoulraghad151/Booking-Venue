<?php

namespace App\Http\Controllers;

use App\Models\EventType;
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
                    'venue_count' => $venues->count(),
                ];
            })
            ->values();

        $eventTypes = EventType::query()
            ->whereHas('spaceLayouts.space', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('venue', fn ($venueQuery) => $venueQuery
                    ->where('status', 'approved')
                    ->where('is_active_by_owner', true))
                ->whereHas('images'))
            ->with([
                'spaceLayouts.space' => fn ($query) => $query
                    ->where('status', 'active')
                    ->whereHas('venue', fn ($venueQuery) => $venueQuery
                        ->where('status', 'approved')
                        ->where('is_active_by_owner', true))
                    ->whereHas('images'),
                'spaceLayouts.space.images' => fn ($query) => $query
                    ->orderByDesc('is_primary')
                    ->orderBy('id'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (EventType $eventType): ?array {
                $space = $eventType->spaceLayouts
                    ->pluck('space')
                    ->filter()
                    ->first(fn ($space) => $space->images->isNotEmpty());

                if (! $space) {
                    return null;
                }

                return [
                    'id' => $eventType->id,
                    'name' => $eventType->name,
                    'description' => $eventType->description,
                    'image_url' => $space->images->first()->image_url,
                ];
            })
            ->filter()
            ->values();

        $featuredVenues = Venue::query()
            ->where('status', 'approved')
            ->where('is_active_by_owner', true)
            ->whereHas('images')
            ->with([
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
            ])
            ->withCount([
                'spaces as active_spaces_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->latest()
            ->limit(8)
            ->get();

        return view('public.home', [
            'locations' => $venues,
            'featuredVenues' => $featuredVenues,
            'occasionCards' => $eventTypes,
        ]);
    }
}

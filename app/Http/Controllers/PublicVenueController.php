<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\View\View;

class PublicVenueController extends Controller
{
    public function show(string $venue): View
    {
        $venue = Venue::query()
            ->where('status', 'approved')
            ->where('is_active_by_owner', true)
            ->with([
                'images',
                'spaces' => fn ($query) => $query
                    ->where('status', 'active')
                    ->with(['images', 'spaceLayouts.eventTypes']),
                    'reviews' => fn ($query) => $query->latest('reviews.created_at'),
            ])
            ->findOrFail($venue);

        $venue->spaces->load([
            'packages' => fn ($query) => $query
                ->where('packages.owner_id', $venue->owner_id)
                ->where('packages.is_active', true)
                ->with([
                    'items' => fn ($query) => $query->whereNull('space_id'),
                ]),
        ]);

        return view('public.venues.show', compact('venue'));
    }
}
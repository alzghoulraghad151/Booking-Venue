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
            ])
            ->findOrFail($venue);

        return view('public.venues.show', compact('venue'));
    }
}
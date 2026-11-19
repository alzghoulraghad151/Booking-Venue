<?php

namespace App\Http\Controllers\Owner;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Http\Requests\Owner\StoreVenueRequest;
use Illuminate\Http\RedirectResponse;



use App\Http\Requests\Owner\UpdateVenueRequest;
use App\Models\Venue;
use Illuminate\Support\Facades\Gate;


class OwnerVenueController extends Controller
{
    public function index(Request $request): View
    {
        $venues = $request->user()
            ->venues()
            ->latest()
            ->paginate(15);

        return view('owner.venues.index', ['venues' => $venues]);
    }


public function show(Venue $venue): View
{
    Gate::authorize('view', $venue);

    $venue->load('spaces');

    return view('owner.venues.show', ['venue' => $venue]);
}



    public function create(): View
{
    return view('owner.venues.create');
}


public function store(StoreVenueRequest $request): RedirectResponse
{
    $request->user()->venues()->create($request->validated());

    return redirect()
        ->route('owner.venues.index')
        ->with('success', 'Venue added and submitted for review.');
}




public function edit(Venue $venue): View
{
    Gate::authorize('update', $venue);

    return view('owner.venues.edit', ['venue' => $venue]);
}




public function update(UpdateVenueRequest $request, Venue $venue): RedirectResponse
{
    $venue->update($request->validated());

    return redirect()
        ->route('owner.venues.index')
        ->with('success', 'Venue updated successfully.');
}


public function updateAvailability(Request $request, Venue $venue): RedirectResponse
{
    Gate::authorize('update', $venue);

    $validated = $request->validate([
        'is_active_by_owner' => ['required', 'boolean'],
    ]);

    $isActive = (bool) $validated['is_active_by_owner'];

    return DB::transaction(function () use ($venue, $isActive) {
        $currentVenue = Venue::query()
            ->lockForUpdate()
            ->findOrFail($venue->getKey());

        Gate::authorize('update', $currentVenue);

        if ($currentVenue->status !== 'approved') {
            return redirect()
                ->route('owner.venues.show', $currentVenue)
                ->withErrors([
                    'is_active_by_owner' => 'Availability can only be changed for approved venues.',
                ]);
        }

        $currentVenue->is_active_by_owner = $isActive;
        $currentVenue->save();

        return redirect()
            ->route('owner.venues.show', $currentVenue)
            ->with('success', $isActive ? 'Venue activated.' : 'Venue deactivated.');
    });
}




public function destroy(Venue $venue): RedirectResponse
{
    return DB::transaction(function () use ($venue) {
        $currentVenue = Venue::query()
            ->lockForUpdate()
            ->findOrFail($venue->getKey());

        Gate::authorize('delete', $currentVenue);

        $hasSpaces = DB::table('spaces')
            ->where('venue_id', $currentVenue->getKey())
            ->exists();

        $hasBookings = DB::table('bookings')
            ->where('venue_id', $currentVenue->getKey())
            ->exists();

        if ($hasSpaces || $hasBookings) {
            return redirect()
                ->route('owner.venues.show', $currentVenue)
                ->withErrors([
                    'archive' => 'Venues with spaces or bookings cannot be archived.',
                ]);
        }

        $currentVenue->delete();

        return redirect()
            ->route('owner.venues.index')
            ->with('success', 'Venue archived successfully.');
    });
}

}
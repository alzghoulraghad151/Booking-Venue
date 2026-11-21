<?php

namespace App\Http\Controllers\Owner;
use Illuminate\Support\Facades\DB;
use App\Actions\Owner\SaveVenue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\HttpClientException;
use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Http\Requests\Owner\StoreVenueRequest;
use Illuminate\Http\RedirectResponse;



use App\Http\Requests\Owner\UpdateVenueRequest;
use App\Models\Venue;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class OwnerVenueController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'inactive'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
        ]);
        $direction = ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc';

        $venues = $request->user()->venues()
            ->when(filled($filters['search'] ?? null), fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%'))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('created_at', $direction)->orderBy('id', $direction)
            ->paginate(9)->withQueryString();

        return view('owner.venues.index', compact('venues', 'filters'));
    }


public function show(Venue $venue): View
{
    Gate::authorize('view', $venue);

    $venue->load(['spaces', 'images']);

    return view('owner.venues.show', ['venue' => $venue]);
}



    public function create(): View
    {
        return view('owner.venues.create', ['addresses' => Venue::addressOptions()]);
    }

    public function store(StoreVenueRequest $request, SaveVenue $saveVenue): RedirectResponse
    {
        return $this->save($request, $saveVenue, null);
    }

    public function edit(Venue $venue): View
    {
        Gate::authorize('update', $venue);
        $venue->load(['images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id')]);

        return view('owner.venues.edit', ['venue' => $venue, 'addresses' => Venue::addressOptions()]);
    }

    public function update(UpdateVenueRequest $request, Venue $venue, SaveVenue $saveVenue): RedirectResponse
    {
        return $this->save($request, $saveVenue, $venue);
    }

    private function save(StoreVenueRequest|UpdateVenueRequest $request, SaveVenue $saveVenue, ?Venue $venue): RedirectResponse
    {
        try {
            $saveVenue->handle(
                $request->user(),
                $venue,
                $request->safe()->only(['name', 'address', 'description']),
                $request->file('images', []),
                array_map('intval', $request->validated('removed_images', [])),
                $request->validated('primary_image'),
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (HttpClientException|RuntimeException $exception) {
            report($exception);

            return back()->withInput($request->safe()->only(['name', 'address', 'description']))->withErrors([
                'images' => 'The venue changes could not be saved. Select the new images and try again.',
            ]);
        }

        return to_route('owner.venues.index')->with('success', $venue
            ? 'Venue updated successfully.'
            : 'Venue added and submitted for review.');
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
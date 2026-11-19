<?php

namespace App\Http\Controllers\Owner;

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




}
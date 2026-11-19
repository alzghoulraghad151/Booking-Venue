<?php

namespace App\Http\Controllers\Owner;

use App\Actions\Owner\AddVenueImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreVenueImageRequest;
use App\Models\Venue;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class OwnerVenueImageController extends Controller
{
    public function store(StoreVenueImageRequest $request, Venue $venue, AddVenueImage $addImage): RedirectResponse
    {
        try {
            $addImage->handle($venue, $request->file('image'));
        } catch (HttpClientException|RuntimeException $exception) {
            report($exception);

            return back()->withErrors(['image' => 'The image could not be saved. Please try again.']);
        }

        return redirect()->route('owner.venues.show', $venue)
            ->with('success', 'Venue image uploaded.');
    }
}

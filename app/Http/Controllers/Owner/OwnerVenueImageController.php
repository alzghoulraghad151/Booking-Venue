<?php

namespace App\Http\Controllers\Owner;

use App\Actions\Owner\AddVenueImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreVenueImageRequest;
use App\Models\Venue;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\RedirectResponse;
use RuntimeException;
use App\Actions\Owner\DeleteVenueImage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;


class OwnerVenueImageController extends Controller
{
    public function store(StoreVenueImageRequest $request, Venue $venue, AddVenueImage $addImage): RedirectResponse
    {
        try {
          $addImage->handle($venue, $request->validated('images'));
        } catch (HttpClientException|RuntimeException $exception) {
            report($exception);

          return back()->withErrors([
    'images' => 'The images could not be saved. Please try again.',
]);
        }

        return redirect()->route('owner.venues.edit', $venue)
            ->with('success', 'Venue image uploaded.');
    }










    public function setPrimary(Venue $venue, int $image): RedirectResponse
{
    DB::transaction(function () use ($venue, $image): void {
        $currentVenue = Venue::query()
            ->lockForUpdate()
            ->findOrFail($venue->getKey());

        Gate::authorize('update', $currentVenue);

        $selectedImage = $currentVenue->images()->findOrFail($image);

      $currentVenue->images()
    ->where('is_primary', true)
    ->where('id', '!=', $selectedImage->getKey())
    ->update(['is_primary' => false]);

$selectedImage->is_primary = true;
$selectedImage->save();
    });

    return redirect()->route('owner.venues.edit', $venue)
        ->with('success', 'Primary photo updated.');
}











public function destroy(Venue $venue, int $image, DeleteVenueImage $deleteImage): RedirectResponse
{
    $deleteImage->handle($venue, $image);

    return redirect()->route('owner.venues.edit', $venue)
        ->with('success', 'Venue image removed.');
}

}

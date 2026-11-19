<?php

namespace App\Actions\Owner;

use App\Models\Venue;
use App\Services\CloudinaryImages;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Throwable;

class AddVenueImage
{
    public function __construct(private CloudinaryImages $cloudinary)
    {
    }

    public function handle(Venue $venue, UploadedFile $image): void
    {
        $publicId = 'convene/venues/'.$venue->getKey().'/'.Str::uuid();
        $url = $this->cloudinary->upload($image, $publicId);

        try {
            DB::transaction(function () use ($venue, $url) {
                $currentVenue = Venue::query()->lockForUpdate()->findOrFail($venue->getKey());
                Gate::authorize('update', $currentVenue);

                $currentVenue->images()->create([
                    'image_url' => $url,
                    'is_primary' => !$currentVenue->images()->exists(),
                ]);
            });
        } catch (Throwable $exception) {
            try {
                $this->cloudinary->destroy($publicId);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            throw $exception;
        }
    }
}

<?php

namespace App\Actions\Owner;

use App\Models\Venue;
use App\Services\CloudinaryImages;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;

use Throwable;

class AddVenueImage
{
    public function __construct(private CloudinaryImages $cloudinary)
    {
    }

 /** @param array<int, UploadedFile> $images */
public function handle(Venue $venue, array $images): void
{

if ($venue->images()->count() + count($images) > 5) {
    throw ValidationException::withMessages([
        'images' => 'A venue can have at most 5 images in total.',
    ]);
}

    $uploaded = [];

    try {
        foreach ($images as $image) {
            $publicId = 'convene/venues/'.$venue->getKey().'/'.Str::uuid();
            $uploaded[$publicId] = $this->cloudinary->upload($image, $publicId);
        }

        DB::transaction(function () use ($venue, $uploaded): void {

        $needsPrimary = $existingCount === 0;

            $currentVenue = Venue::query()
                ->lockForUpdate()
                ->findOrFail($venue->getKey());

           Gate::authorize('update', $currentVenue);

$existingCount = $currentVenue->images()->count();

if ($existingCount + count($uploaded) > 5) {
    throw ValidationException::withMessages([
        'images' => 'A venue can have at most 5 images in total.',
    ]);
}

$needsPrimary = $existingCount === 0;

foreach ($uploaded as $publicId => $url) {
    $currentVenue->images()->create([
        'image_url' => $url,
        'cloudinary_public_id' => $publicId,
        'is_primary' => $needsPrimary,
    ]);

    $needsPrimary = false;
}
        });
    } catch (Throwable $exception) {
        foreach (array_keys($uploaded) as $publicId) {
            try {
                $this->cloudinary->destroy($publicId);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
        }

        throw $exception;
    }
}
}

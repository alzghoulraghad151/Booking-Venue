<?php

namespace App\Actions\Owner;

use App\Models\Venue;
use App\Services\CloudinaryImages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteVenueImage
{
    public function __construct(private CloudinaryImages $cloudinary)
    {
    }

    public function handle(Venue $venue, int $imageId): void
    {
        $publicId = DB::transaction(function () use ($venue, $imageId): string {
            $currentVenue = Venue::query()
                ->lockForUpdate()
                ->findOrFail($venue->getKey());

            Gate::authorize('update', $currentVenue);

            $image = $currentVenue->images()->findOrFail($imageId);

            if ($currentVenue->images()->count() <= 1) {
                throw ValidationException::withMessages([
                    'delete_image' => 'A venue must keep at least one image.',
                ]);
            }

            if (blank($image->cloudinary_public_id)) {
                throw ValidationException::withMessages([
                    'delete_image' => 'This image is missing its Cloudinary ID.',
                ]);
            }

            $publicId = $image->cloudinary_public_id;
            $wasPrimary = $image->is_primary;

            $image->delete();

            if ($wasPrimary) {
                $nextImage = $currentVenue->images()->orderBy('id')->firstOrFail();
                $nextImage->update(['is_primary' => true]);
            }

            return $publicId;
        });

        try {
            $this->cloudinary->destroy($publicId);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
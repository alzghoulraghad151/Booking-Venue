<?php

namespace App\Actions\Owner;

use App\Models\User;
use App\Services\CloudinaryImages;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CreateVenue
{
    public function __construct(private CloudinaryImages $cloudinary)
    {
    }

    public function handle(User $owner, array $attributes, UploadedFile $image): void
    {
        $publicId = 'convene/venues/'.Str::uuid();
        $url = $this->cloudinary->upload($image, $publicId);

        try {
            DB::transaction(function () use ($owner, $attributes, $url, $publicId): void {
                $venue = $owner->venues()->create($attributes);
                $venue->images()->create([
                    'image_url' => $url,
                    'cloudinary_public_id' => $publicId,
                    'is_primary' => true,
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

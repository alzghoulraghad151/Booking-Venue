<?php

namespace App\Actions\Owner;

use App\Models\User;
use App\Models\Venue;
use App\Services\CloudinaryImages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaveVenue
{
    public function __construct(private CloudinaryImages $cloudinary) {}

    public function handle(User $owner, ?Venue $venue, array $attributes, array $files, array $removed, ?string $primary): void
    {
        $uploaded = [];

        try {
            $deleted = DB::transaction(function () use ($owner, $venue, $attributes, $files, $removed, $primary, &$uploaded): array {
                if ($venue) {
                    $venue = Venue::query()->lockForUpdate()->findOrFail($venue->id);
                    Gate::authorize('update', $venue);
                }

                $current = $venue ? $venue->images()->orderByDesc('is_primary')->orderBy('id')->get() : collect();
                $remaining = $current->reject(fn ($image) => in_array((int) $image->id, $removed, true));
                $count = $remaining->count() + count($files);

                if ($current->count() - $remaining->count() !== count($removed)) {
                    throw ValidationException::withMessages(['images' => 'The gallery changed. Reopen the form and try again.']);
                }

                if ($count < 1 || $count > Venue::IMAGE_LIMIT) {
                    throw ValidationException::withMessages(['images' => 'Keep between one and three venue images.']);
                }

                $choices = $remaining->map(fn ($image) => 'saved:'.$image->id)->all();
                foreach (array_keys($files) as $index) {
                    $choices[] = 'new:'.$index;
                }

                if ($primary !== null && ! in_array($primary, $choices, true)) {
                    throw ValidationException::withMessages(['images' => 'Select a primary image from the current gallery.']);
                }
                $primary ??= $choices[0];

                if ($venue) {
                    $venue->update($attributes);
                } else {
                    $venue = $owner->venues()->create($attributes);
                }

                foreach ($remaining as $image) {
                    $image->update(['is_primary' => $primary === 'saved:'.$image->id]);
                }

                foreach ($files as $index => $file) {
                    $publicId = 'convene/venues/'.$venue->id.'/'.Str::uuid();
                    $uploaded[] = $publicId;
                    $url = $this->cloudinary->upload($file, $publicId);
                    $venue->images()->create([
                        'image_url' => $url,
                        'cloudinary_public_id' => $publicId,
                        'is_primary' => $primary === 'new:'.$index,
                    ]);
                }

                $deleted = [];
                foreach ($current->diff($remaining) as $image) {
                    if (filled($image->cloudinary_public_id)) {
                        $deleted[] = $image->cloudinary_public_id;
                    }
                    $image->delete();
                }

                return $deleted;
            });
        } catch (Throwable $exception) {
            $this->cleanUp($uploaded);
            throw $exception;
        }

        $this->cleanUp($deleted);
    }

    private function cleanUp(array $publicIds): void
    {
        foreach ($publicIds as $publicId) {
            try {
                $this->cloudinary->destroy($publicId);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}

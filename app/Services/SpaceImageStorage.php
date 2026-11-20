<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Throwable;

class SpaceImageStorage
{
    public function __construct(private CloudinaryImages $cloudinary)
    {
    }

    /**
     * @param array<int, UploadedFile> $images
     * @return array<int, array{image_url: string, cloudinary_public_id: string}>
     */
    public function upload(array $images, int $spaceId): array
    {
        $uploaded = [];
        $publicIds = [];

        try {
            foreach ($images as $image) {
                $publicId = 'convene/spaces/'.$spaceId.'/'.Str::uuid();
                $publicIds[] = $publicId;

                $uploaded[] = [
                    'image_url' => $this->cloudinary->upload($image, $publicId),
                    'cloudinary_public_id' => $publicId,
                ];
            }
        } catch (Throwable $exception) {
            $this->destroyCloudinaryImages($publicIds);

            throw $exception;
        }

        return $uploaded;
    }

    /**
     * @param array<int, array{image_url: string, cloudinary_public_id: string}> $images
     */
    public function discard(array $images): void
    {
        $this->destroyCloudinaryImages(array_column($images, 'cloudinary_public_id'));
    }

    /**
     * @param array<int, array{image_url: string, cloudinary_public_id: string}> $images
     */
    public function delete(array $images): void
    {
        $this->destroyCloudinaryImages(array_column($images, 'cloudinary_public_id'));
    }

    /**
     * @param array<int, string> $publicIds
     */
    private function destroyCloudinaryImages(array $publicIds): void
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

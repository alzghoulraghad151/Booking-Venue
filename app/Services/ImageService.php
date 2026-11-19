<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageService
{
    public function store(UploadedFile $image, string $directory): string
    {
        $path = $image->storePublicly($directory, 'public');

        if (! is_string($path)) {
            throw new RuntimeException('The image could not be stored.');
        }

        return $path;
    }

    public function delete(string $path): void
    {
        if (! str_starts_with($path, 'spaces/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}

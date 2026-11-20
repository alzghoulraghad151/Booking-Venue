<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryImages
{
    public function upload(UploadedFile $image, string $publicId): string
    {
        $stream = fopen($image->getPathname(), 'rb');

        if ($stream === false) {
            throw new RuntimeException('Could not read the uploaded image.');
        }

        try {
            $url = $this->client()
                ->attach('file', $stream, 'uploaded-image')
                ->post('upload', ['public_id' => $publicId, 'overwrite' => 'false'])
                ->throw()
                ->json('secure_url');
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (!is_string($url) || !str_starts_with($url, 'https://') || strlen($url) > 500) {
            throw new RuntimeException('Cloudinary returned an invalid image URL.');
        }

        return $url;
    }

    public function destroy(string $publicId): void
    {
        $result = $this->client()->asForm()
            ->post('destroy', ['public_id' => $publicId])
            ->throw()
            ->json('result');

        if (!in_array($result, ['ok', 'not found'], true)) {
            throw new RuntimeException('Cloudinary could not remove the venue image.');
        }
    }

    private function client(): PendingRequest
    {
        $cloud = config('services.cloudinary.cloud_name');
        $key = config('services.cloudinary.api_key');
        $secret = config('services.cloudinary.api_secret');

        if (!$cloud || !$key || !$secret) {
            throw new RuntimeException('Cloudinary credentials are missing.');
        }

        return Http::baseUrl('https://api.cloudinary.com/v1_1/'.rawurlencode($cloud).'/image')
            ->withBasicAuth($key, $secret)
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(20);
    }
}

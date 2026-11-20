<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ElevenLabsTranscription
{
    public function transcribe(string $audioPath): string
    {
        $audio = fopen($audioPath, 'rb');

        if ($audio === false) {
            throw new RuntimeException('Could not open the audio file.');
        }

        try {
            $response = Http::withHeaders([
                'xi-api-key' => config('services.elevenlabs.api_key'),
            ])->acceptJson()
                ->connectTimeout(5)
                ->timeout(30)
                ->attach('file', $audio, basename($audioPath))
                ->post('https://api.elevenlabs.io/v1/speech-to-text', [
                    'model_id' => 'scribe_v2',
                ])
                ->throw();

            return trim($response->json('text', ''));
        } finally {
            if (is_resource($audio)) {
                fclose($audio);
            }
        }
    }
}
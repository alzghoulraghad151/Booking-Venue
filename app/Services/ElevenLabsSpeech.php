<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ElevenLabsSpeech
{
    public function synthesize(string $text): string
    {
        $apiKey = config('services.elevenlabs.api_key');
        $voiceId = config('services.elevenlabs.voice_id');

        if (blank($apiKey) || blank($voiceId)) {
            throw new RuntimeException('ElevenLabs API key or voice ID is missing.');
        }

        return Http::withHeaders(['xi-api-key' => $apiKey])
            ->accept('audio/mpeg')
            ->connectTimeout(5)
            ->timeout(20)
            ->post('https://api.elevenlabs.io/v1/text-to-speech/'.rawurlencode($voiceId), [
                'text' => $text,
                'model_id' => 'eleven_multilingual_v2',
            ])
            ->throw()
            ->body();
    }
}
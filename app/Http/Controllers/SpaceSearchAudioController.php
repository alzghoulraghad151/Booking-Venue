<?php

namespace App\Http\Controllers;

use App\Services\ElevenLabsSpeech;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class SpaceSearchAudioController extends Controller
{
    public function __invoke(Request $request, ElevenLabsSpeech $speech): Response
    {
        $data = $request->validate([
            'summary' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $audio = $speech->synthesize($data['summary']);
        } catch (HttpClientException | RuntimeException $exception) {
            logger()->warning('Search summary audio generation failed.', [
                'exception_type' => $exception::class,
            ]);

            abort(502, 'تعذّر تحميل الملخّص الصوتي.');
        }

        return response($audio, 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
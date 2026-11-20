<?php

namespace App\Http\Controllers;

use App\Services\ElevenLabsTranscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use App\Services\SpaceSearchFilterExtractor;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Validation\ValidationException;
use JsonException;

class SpaceVoiceSearchController extends Controller
{
    public function transcribe(
    Request $request,
    ElevenLabsTranscription $service,
    SpaceSearchFilterExtractor $extractor,
): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'mimes:webm,ogg,m4a,mp4,mp3,wav', 'max:5120'],
        ]);

        try {
            $text = $service->transcribe($request->file('audio')->getPathname());
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'تعذّر تحويل التسجيل إلى نص. جرّبي مرة ثانية.',
            ], 502);
        }

       $filters = null;

if (filled($text)) {
    try {
        $filters = $extractor->extract($text);
    } catch (HttpClientException | RuntimeException | JsonException | ValidationException $exception) {
        logger()->warning('Voice search filter extraction failed.', [
            'exception_type' => $exception::class,
        ]);
    }
}

return response()->json([
    'text' => $text,
    'filters' => $filters,
]);
    }
}
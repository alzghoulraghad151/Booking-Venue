<?php

namespace App\Http\Controllers;

use App\Services\MeetingInvitationExtractor;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use JsonException;
use RuntimeException;

use App\Models\Space;
use Carbon\CarbonImmutable;


class BookingInvitationController extends Controller
{
    public function analyze(Request $request, MeetingInvitationExtractor $extractor): Response
    {
        $data = $request->validate([
            'invitation' => ['required', 'string', 'max:6000'],
        ]);

        try {
            $draft = $extractor->extract($data['invitation']);
        } catch (HttpClientException | JsonException | RuntimeException | ValidationException) {
            return response()->view('public.bookings.from-invite', [
                'invitation' => $data['invitation'],
                'analysisError' => 'تعذّر تحليل الدعوة. حاولي مرة ثانية.',
            ], 502);
        }

        return response()->view('public.bookings.from-invite', [
            'invitation' => $data['invitation'],
            'draft' => $draft,
        ]);
    }



    public function suggest(Request $request): Response
{
    $data = $request->validate([
        'city' => ['required', 'string', 'max:100'],
        'date' => ['required', 'date_format:Y-m-d'],
        'start_time' => ['required', 'date_format:H:i'],
        'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        'in_person_count' => ['required', 'integer', 'min:1', 'max:100000'],
        'result_language' => ['nullable', 'in:ar,en'],
    ]);

    $start = CarbonImmutable::createFromFormat(
        '!Y-m-d H:i',
        $data['date'].' '.$data['start_time'],
        'Asia/Amman'
    );

    if ($start->lessThanOrEqualTo(CarbonImmutable::now('Asia/Amman'))) {
        throw ValidationException::withMessages([
            'date' => 'اختاري موعد اجتماع بالمستقبل.',
        ]);
    }

    $end = $start->addMinutes((int) $data['duration_minutes']);

    if ($end->toDateString() !== $start->toDateString()) {
        throw ValidationException::withMessages([
            'duration_minutes' => 'اختاري مدة تنتهي في نفس يوم الاجتماع.',
        ]);
    }

    $startUtc = $start->utc();
    $endUtc = $end->utc();

    $city = preg_replace('/\p{Mn}/u', '', trim($data['city']));
    $cityNames = match (mb_strtolower($city)) {
        'عمان', 'amman' => ['عمان', 'عمّان', 'Amman'],
        'اربد', 'إربد', 'irbid' => ['اربد', 'إربد', 'Irbid'],
        'عجلون', 'ajloun' => ['عجلون', 'Ajloun'],
        default => [$data['city']],
    };

    $guests = (int) $data['in_person_count'];

    $suggestions = Space::query()
        ->with('venue')
        ->where('status', 'active')
        ->where('capacity', '>=', $guests)
        ->whereHas('venue', function ($venue) use ($cityNames) {
            $venue->where('status', 'approved')
                ->where('is_active_by_owner', true)
                ->where(function ($address) use ($cityNames) {
                    foreach ($cityNames as $name) {
                        $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $name);
                        $address->orWhereRaw("address LIKE ? ESCAPE '!'", ['%'.$term.'%']);
                    }
                });
        })
        ->whereHas('spaceLayouts', fn ($layout) => $layout->where('capacity', '>=', $guests))
        ->whereDoesntHave('bookingDetails', function ($details) use ($startUtc, $endUtc) {
            $details->where('start_at', '<', $endUtc)
                ->where('end_at', '>', $startUtc)
                ->whereHas('booking', fn ($booking) => $booking->where('status', 'confirmed'));
        })
        ->orderBy('capacity')
        ->orderBy('id')
        ->limit(5)
        ->get();

    return response()->view('public.bookings.from-invite', [
        'draft' => $data,
        'suggestions' => $suggestions,
        'bookingPrefill' => [
            'date' => $start->toDateString(),
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'guest_count' => $guests,
        ],
    ]);
}

}

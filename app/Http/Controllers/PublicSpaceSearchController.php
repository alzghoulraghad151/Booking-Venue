<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSpaceSearchController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'event_type_id' => ['nullable', 'integer', 'exists:event_types,id'],
            'lang' => ['nullable', 'in:ar,en'],
        ]);

        $address = $filters['address'] ?? '';

         $isEnglish = ($filters['lang'] ?? 'ar') === 'en';
        $capacity = $filters['capacity'] ?? null;
        $eventTypeId = $filters['event_type_id'] ?? null;
        $searched = $address !== '' || $capacity !== null || $eventTypeId !== null;
        $spaces = collect();

        if ($searched) {
            $spaces = Space::query()
                ->with(['venue', 'images'])
                ->where('status', 'active')
                ->whereHas('venue', function ($query) use ($address){
                    $query->where('status', 'approved')
                        ->where('is_active_by_owner', true);

                    if ($address !== '') {
    $query->where('address', 'like', '%'.$address.'%');
}
                })
                ->when($capacity !== null, fn ($query) => $query->where('capacity', '>=', $capacity))
                ->when($eventTypeId !== null, fn ($query) => $query->whereHas(
                    'spaceLayouts.eventTypes',
                    fn ($eventTypeQuery) => $eventTypeQuery->whereKey($eventTypeId)
                ))
                ->orderBy('capacity')
                ->orderBy('id')
                ->paginate(12)
                ->withQueryString();
        }


     $voiceSummary = null;

if ($searched && $request->boolean('voice') && $spaces->currentPage() === 1) {
    $topSpaces = $spaces->getCollection()->take(3);

    if ($topSpaces->isEmpty()) {
        $voiceSummary = $isEnglish
            ? 'We found no matching spaces. Try a different location or guest count.'
            : 'ما لقينا مساحات بتناسب طلبك. جرّبي تعدّلي الموقع أو عدد الأشخاص.';
    } else {
        $intro = match ($topSpaces->count()) {
            1 => $isEnglish ? 'We found one matching space.' : 'لقينا إلك مساحة مناسبة.',
            2 => $isEnglish ? 'We found two matching spaces.' : 'لقينا إلك مساحتين مناسبات.',
            3 => $isEnglish ? 'Here are the three closest matches.' : 'هاي أقرب ثلاث مساحات للعدد اللي طلبته.',
        };

        $details = $topSpaces->map(function (Space $space) use ($isEnglish): string {
            $name = \Illuminate\Support\Str::limit($space->name, 60);
            $venue = \Illuminate\Support\Str::limit($space->venue->name, 60);

            return $isEnglish
                ? "{$name} at {$venue} can accommodate {$space->capacity} guests."
                : "مساحة {$name} في {$venue}، بتسع {$space->capacity} شخص.";
        })->implode(' ');

        $voiceSummary = $intro.' '.$details;

        if ($spaces->total() > $topSpaces->count()) {
            $voiceSummary .= $isEnglish
                ? ' More options are listed in the search results.'
                : ' وإذا بدك خيارات أكثر، باقي المساحات موجودة بنتائج البحث.';
        }
    }
}

$voiceSummaryAudioUrl = $voiceSummary === null
    ? null
    : \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'spaces.search.summary-audio',
        now()->addMinutes(5),
        ['summary' => $voiceSummary],
    );


       return view('public.spaces.search', compact(
    'address',
    'capacity',
    'eventTypeId',
    'searched',
    'spaces',
    'voiceSummary',
    'voiceSummaryAudioUrl',

    'isEnglish'
));
    }
}

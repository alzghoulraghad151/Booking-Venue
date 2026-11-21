<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerCalendarRequest;
use App\Models\BookingDetail;
use App\Models\Space;
use App\Models\Venue;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class OwnerCalendarController extends Controller
{
    public function index(OwnerCalendarRequest $request): View
    {
        $filters = $request->validated();
        $timezone = 'Asia/Amman';
        $today = CarbonImmutable::today($timezone);
        $anchor = isset($filters['date'])
            ? CarbonImmutable::createFromFormat('!Y-m-d', $filters['date'], $timezone)
            : $today;
        $mode = $filters['view'] ?? 'week';
        $spaceId = isset($filters['space_id']) ? (int) $filters['space_id'] : null;

        [$start, $end, $previous] = match ($mode) {
            'day' => [$anchor, $anchor->addDay(), $anchor->subDay()],
            'month' => [$anchor->startOfMonth(), $anchor->startOfMonth()->addMonth(), $anchor->startOfMonth()->subMonth()],
            default => [$anchor->startOfWeek(), $anchor->startOfWeek()->addWeek(), $anchor->startOfWeek()->subWeek()],
        };

        $venues = Venue::query()->where('owner_id', $request->user()->getKey())->select('id');
        $spaces = Space::query()->whereIn('venue_id', clone $venues)
            ->with('venue:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'venue_id']);

        abort_if($spaceId !== null && ! $spaces->contains('id', $spaceId), 404);

        $details = BookingDetail::query()
            ->whereIn('space_id', $spaces->modelKeys())
            ->when($spaceId !== null, fn ($query) => $query->where('space_id', $spaceId))
            ->whereHas('booking', fn ($query) => $query->whereIn('venue_id', clone $venues))
            ->where('start_at', '<', $end->utc())
            ->where('end_at', '>', $start->utc())
            ->with(['booking', 'space'])
            ->orderBy('start_at')
            ->orderBy('id')
            ->get();

        $days = [];
        for ($day = $start; $day->lt($end); $day = $day->addDay()) {
            $days[$day->toDateString()] = ['date' => $day, 'events' => []];
        }

        foreach ($details as $detail) {
            $eventStart = $detail->start_at->toImmutable()->setTimezone($timezone);
            $eventEnd = $detail->end_at->toImmutable()->setTimezone($timezone);
            $firstDay = $eventStart->max($start)->startOfDay();
            $lastBoundary = $eventEnd->min($end);

            for ($day = $firstDay; $day->lt($lastBoundary); $day = $day->addDay()) {
                $days[$day->toDateString()]['events'][] = [
                    'detail' => $detail,
                    'start' => $eventStart->max($day),
                    'end' => $eventEnd->min($day->addDay()),
                    'overlap' => false,
                ];
            }
        }

        foreach ($days as &$day) {
            $this->markOverlaps($day['events']);
        }
        unset($day);

        return view('owner.calendar', compact(
            'anchor', 'today', 'mode', 'start', 'end', 'previous', 'spaceId', 'spaces', 'days',
        ));
    }

    private function markOverlaps(array &$events): void
    {
        $latestBySpace = [];
        foreach ($events as $index => $event) {
            if (! in_array($event['detail']->booking->status, ['pending', 'confirmed', 'completed'], true)) {
                continue;
            }

            $spaceId = $event['detail']->space_id;
            $previousIndex = $latestBySpace[$spaceId] ?? null;

            if ($previousIndex !== null && $events[$previousIndex]['end']->gt($event['start'])) {
                $events[$previousIndex]['overlap'] = true;
                $events[$index]['overlap'] = true;
            }

            if ($previousIndex === null || $event['end']->gt($events[$previousIndex]['end'])) {
                $latestBySpace[$spaceId] = $index;
            }
        }
    }
}

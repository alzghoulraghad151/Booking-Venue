<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveEventTypeRequest;
use App\Models\EventType;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEventTypeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search', '');
        abort_unless(is_string($search) && mb_strlen($search) <= 100, 422);
        $search = trim($search);

        $eventTypes = EventType::withCount('bookingDetails')
            ->when($search !== '', fn ($query) => $query->whereLike('name', '%'.$search.'%'))
            ->orderBy('name')
            ->get();

        return view('admin.event-types.index', compact('eventTypes', 'search'));
    }

    public function create(): View
    {
        return view('admin.event-types.form', [
            'eventType' => new EventType(),
            'approvedNames' => EventType::APPROVED_NAMES,
        ]);
    }

    public function store(SaveEventTypeRequest $request): RedirectResponse
    {
        try {
            EventType::create($request->validated());
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors(['name' => 'This event type already exists.']);
        }

        return to_route('admin.event-types.index')->with('status', 'Event type added.');
    }

    public function edit(EventType $eventType): View
    {
        return view('admin.event-types.form', [
            'eventType' => $eventType,
            'approvedNames' => EventType::APPROVED_NAMES,
        ]);
    }

    public function update(SaveEventTypeRequest $request, EventType $eventType): RedirectResponse
    {
        try {
            $eventType->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors(['name' => 'This event type already exists.']);
        }

        return to_route('admin.event-types.index')->with('status', 'Event type updated.');
    }

    public function destroy(EventType $eventType): RedirectResponse
    {
        try {
            $eventType->delete();
        } catch (QueryException $exception) {
            $driverCode = (int) ($exception->errorInfo[1] ?? 0);
            $isForeignKeyViolation = $driverCode === 1451
                || ($driverCode === 19 && str_contains($exception->errorInfo[2] ?? '', 'FOREIGN KEY'));

            if (! $isForeignKeyViolation) {
                throw $exception;
            }

            return to_route('admin.event-types.index')->withErrors([
                'delete' => 'This event type is linked to a booking, layout, or package and cannot be deleted.',
            ]);
        }

        return to_route('admin.event-types.index')->with('status', 'Event type deleted.');
    }
}

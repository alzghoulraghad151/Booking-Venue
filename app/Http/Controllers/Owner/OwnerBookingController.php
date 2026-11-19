<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Venue;
use App\Services\AvailabilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerBookingController extends Controller
{
    private const RELATIONS = [
        'customer',
        'venue',
        'details.space.venue',
        'details.spaceLayout',
        'details.eventType',
        'details.package',
        'payments',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'owner_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'venue_owner')),
            ],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,rejected'],
            'date' => ['nullable', 'date'],
            'organizer' => ['nullable', 'string', 'max:100'],
        ]);

        $owners = User::query()
            ->where('role', 'venue_owner')
            ->orderBy('name')
            ->get(['id', 'name']);
        $ownerId = isset($filters['owner_id']) ? (int) $filters['owner_id'] : null;

        $query = Booking::query()
            ->with(self::RELATIONS)
            ->withCount('details')
            ->whereHas('venue', fn (Builder $venue) => $venue->where('owner_id', $ownerId ?? 0));

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date'])) {
            $query->whereHas('details', fn (Builder $details) => $details->whereDate('start_at', $filters['date']));
        }

        if (! empty($filters['organizer'])) {
            $search = '%'.$filters['organizer'].'%';
            $query->whereHas('customer', function (Builder $customer) use ($search) {
                $customer->where(function (Builder $nameOrEmail) use ($search) {
                    $nameOrEmail->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            });
        }

        $bookings = $query->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
        $selectedOwner = $ownerId ? $owners->firstWhere('id', $ownerId) : null;

        return view('owner.bookings.list', compact('bookings', 'owners', 'selectedOwner', 'ownerId', 'filters'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $ownerId = (int) $request->validate([
            'owner_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'venue_owner')),
            ],
        ])['owner_id'];

        $booking = Booking::query()
            ->with([...self::RELATIONS, 'review'])
            ->whereKey($booking->getKey())
            ->whereHas('venue', fn (Builder $venue) => $venue->where('owner_id', $ownerId))
            ->firstOrFail();

        $owner = User::query()->findOrFail($ownerId);
        $totals = [
            'amount' => $booking->details->sum('total_price'),
            'paid' => $booking->payments->where('status', 'paid')->sum('amount'),
        ];

        return view('owner.bookings.details', compact('booking', 'owner', 'totals'));
    }

    public function confirm(
        Request $request,
        Booking $booking,
        AvailabilityService $availability
    ): RedirectResponse {
        $ownerId = $this->validatedOwnerId($request);
        $failure = $availability->confirm($booking->getKey(), $ownerId);

        if ($failure) {
            return $this->backToBooking($booking, $ownerId)->withErrors(['booking' => $failure]);
        }

        return $this->backToBooking($booking, $ownerId)->with('success', 'Booking confirmed.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $ownerId = $this->validatedOwnerId($request);
        $wasRejected = DB::transaction(function () use ($booking, $ownerId): bool {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->getKey());
            $venue = Venue::query()->lockForUpdate()->findOrFail($lockedBooking->venue_id);

            abort_unless((int) $venue->owner_id === $ownerId, 404);

            if ($lockedBooking->status !== 'pending') {
                return false;
            }

            $lockedBooking->update(['status' => 'rejected']);

            return true;
        });

        if (! $wasRejected) {
            return $this->backToBooking($booking, $ownerId)
                ->withErrors(['booking' => 'Only pending bookings can be rejected.']);
        }

        return $this->backToBooking($booking, $ownerId)->with('success', 'Booking rejected.');
    }

    private function validatedOwnerId(Request $request): int
    {
        return (int) $request->validate([
            'owner_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'venue_owner')),
            ],
        ])['owner_id'];
    }

    private function backToBooking(Booking $booking, int $ownerId): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('owner.bookings.show', [
            'booking' => $booking->getKey(),
            'owner_id' => $ownerId,
        ]);
    }
}

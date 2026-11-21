<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Venue;
use App\Services\AvailabilityService;
use App\Models\Space;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerBookingController extends Controller
{
    private const PLATFORM_COMMISSION_RATE = 10;

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
        $owner = $request->user();

        $ownerVenueIds = Venue::withTrashed()
            ->where('owner_id', $owner->getKey())
            ->pluck('id');

        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,rejected'],
            'date' => ['nullable', 'date'],
            'organizer' => ['nullable', 'string', 'max:100'],

           
            'space_id' => [
                'nullable',
                'integer',
                Rule::exists('spaces', 'id')->where(
                    fn ($q) => $q->whereIn('venue_id', $ownerVenueIds)
                ),
            ],

            'sort' => [
                'nullable',
                'in:newest,oldest,event_date_asc,event_date_desc,total_desc,total_asc,guests_desc',
            ],
        ]);

        $query = Booking::query()
            ->with(self::RELATIONS)
            ->withCount('details')
            ->withMin('details', 'start_at')
            ->withSum('details', 'total_price')
            ->withSum('details', 'guest_count')
            ->whereHas('venue', fn (Builder $venue) =>
                $venue->where('owner_id', $owner->getKey())
            );

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by space.
        if (!empty($filters['space_id'])) {
            $query->whereHas('details', fn (Builder $details) =>
                $details->where('space_id', $filters['space_id'])
            );
        }

        // Filter by event date.
        if (!empty($filters['date'])) {
            $query->whereHas('details', fn (Builder $details) =>
                $details->whereDate('start_at', $filters['date'])
            );
        }

        // Search by organizer name or email.
        if (!empty($filters['organizer'])) {
            $search = '%' . $filters['organizer'] . '%';

            $query->whereHas('customer', function (Builder $customer) use ($search) {
                $customer->where(function (Builder $nameOrEmail) use ($search) {
                    $nameOrEmail->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            });
        }

        // Sorting.
        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),

            'event_date_asc' => $query
                ->orderBy('details_min_start_at')
                ->orderByDesc('id'),

            'event_date_desc' => $query
                ->orderByDesc('details_min_start_at')
                ->orderByDesc('id'),

            'total_desc' => $query
                ->orderByDesc('details_sum_total_price')
                ->orderByDesc('id'),

            'total_asc' => $query
                ->orderBy('details_sum_total_price')
                ->orderByDesc('id'),

            'guests_desc' => $query
                ->orderByDesc('details_sum_guest_count')
                ->orderByDesc('id'),

            default => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id'),
        };

        $bookings = $query->paginate(10)->withQueryString();

        // Get all spaces belonging to the owner's venues.
        $spaces = Space::query()
            ->with(['venue' => fn ($venue) => $venue->withTrashed()])
            ->whereIn('venue_id', $ownerVenueIds)
            ->orderBy('name')
            ->get();

        return view(
            'owner.bookings.list',
            compact('bookings', 'filters', 'spaces')
        );
    }

    public function show(Request $request, Booking $booking): View
    {
        $owner = $request->user();

        $booking = Booking::query()
            ->with([...self::RELATIONS, 'review'])
            ->whereKey($booking->getKey())
            ->whereHas('venue', fn (Builder $venue) =>
                $venue->where('owner_id', $owner->getKey())
            )
            ->firstOrFail();

        // Calculate the total booking amount.
        $bookingAmount = (float) $booking->details->sum('total_price');

        // Platform commission rate.
        $commissionRate = self::PLATFORM_COMMISSION_RATE;

        // Cancelled and rejected bookings have no commission.
        $commission = in_array($booking->status, ['cancelled', 'rejected'])
            ? 0
            : round($bookingAmount * ($commissionRate / 100), 2);

        // Calculate booking totals.
        $totals = [
            'amount' => $bookingAmount,

            'paid' => $booking->payments
                ->where('status', 'paid')
                ->sum('amount'),

            'commission_rate' => $commissionRate,

            'commission' => $commission,

            'owner_earnings' => round(
                $bookingAmount - $commission,
                2
            ),
        ];

        return view(
            'owner.bookings.details',
            compact('booking', 'owner', 'totals')
        );
    }

    public function confirm(
        Request $request,
        Booking $booking,
        AvailabilityService $availability
    ): RedirectResponse {
        $failure = $availability->confirm(
            $booking->getKey(),
            (int) $request->user()->getKey()
        );

        if ($failure) {
            return $this->backToBooking($booking)
                ->withErrors(['booking' => $failure]);
        }

        return $this->backToBooking($booking)
            ->with('success', 'Booking confirmed.');
    }

    public function reject(
        Request $request,
        Booking $booking
    ): RedirectResponse {
        $ownerId = (int) $request->user()->getKey();

        $wasRejected = DB::transaction(function () use ($booking, $ownerId): bool {

            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->getKey());

            $venue = Venue::withTrashed()
                ->lockForUpdate()
                ->findOrFail($lockedBooking->venue_id);

            abort_unless(
                (int) $venue->owner_id === $ownerId,
                404
            );

            if ($lockedBooking->status !== 'pending') {
                return false;
            }

            $lockedBooking->update([
                'status' => 'rejected',
            ]);

            return true;
        });

        if (!$wasRejected) {
            return $this->backToBooking($booking)
                ->withErrors([
                    'booking' => 'Only pending bookings can be rejected.',
                ]);
        }

        return $this->backToBooking($booking)
            ->with('success', 'Booking rejected.');
    }

    private function backToBooking(Booking $booking): RedirectResponse
    {
        return redirect()->route(
            'owner.bookings.show',
            $booking
        );
    }
}

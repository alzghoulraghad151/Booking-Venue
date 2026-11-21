<?php
namespace App\Http\Controllers\Owner;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicBookingDetailsRequest;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Venue;
use App\Services\AvailabilityService;
use App\Services\BookingPriceCalculator;
use App\Models\Space;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerBookingController extends Controller
{
    /** The only booking statuses in the system. */
    public const STATUSES = ['pending', 'confirmed', 'completed', 'rejected'];

    private const RELATIONS = [
        'customer',
        'venue',
        'details.space.venue',
        'details.spaceLayout',
        'details.eventType',
        'details.package',
    ];

    public function index(Request $request): View
    {
        $owner = $request->user();

        $ownerVenueIds = Venue::withTrashed()
            ->where('owner_id', $owner->getKey())
            ->pluck('id');

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'date' => ['nullable', 'date_format:Y-m-d'],
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

        // Guests of a booking = sum of the capacities of the layouts chosen in its details.
        $guestsSubquery = BookingDetail::query()
            ->selectRaw('COALESCE(SUM(COALESCE(space_layouts.capacity, booking_details.guest_count)), 0)')
            ->leftJoin('space_layouts', 'space_layouts.id', '=', 'booking_details.space_layout_id')
            ->whereColumn('booking_details.booking_id', 'bookings.id');

        $query = Booking::query()
            ->select('bookings.*')
            ->with(self::RELATIONS)
            ->withCount('details')
            ->withMin('details', 'start_at')
            ->withSum('details', 'total_price')
            ->addSelect(['guests_total' => $guestsSubquery])
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

        // Filter by event date. Dates are stored in UTC but the owner picks a
        // day in Amman time, so compare against that day's UTC range.
        if (!empty($filters['date'])) {
            $dayStart = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $filters['date'],
                PublicBookingDetailsRequest::TIMEZONE
            )->utc();

            $query->whereHas('details', fn (Builder $details) =>
                $details->where('start_at', '>=', $dayStart)
                    ->where('start_at', '<', $dayStart->addDay())
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
                ->orderByDesc('guests_total')
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

        $statuses = self::STATUSES;

        return view(
            'owner.bookings.list',
            compact('bookings', 'filters', 'spaces', 'statuses')
        );
    }

    public function show(Request $request, Booking $booking): View
    {
        $owner = $request->user();

        $booking = Booking::query()
            ->with([
                ...self::RELATIONS,
                'review',
                // Only the successful payment counts. Failed or abandoned attempts are
                // ignored: the organizer simply tries again.
                'payments' => fn ($payments) => $payments->where('status', 'paid')->orderBy('id'),
            ])
            ->whereKey($booking->getKey())
            ->whereHas('venue', fn (Builder $venue) =>
                $venue->where('owner_id', $owner->getKey())
            )
            ->firstOrFail();

        $totals = $this->totals($booking);

        return view(
            'owner.bookings.details',
            compact('booking', 'owner', 'totals')
        );
    }

    /**
     * Money figures of one booking, all in JOD.
     *
     * Payment works as one successful payment for the whole booking amount
     * (a failed attempt can be retried), so only the "paid" payment is read.
     *
     * - amount / commission come from the booking details (frozen when the booking is created).
     * - paid is the amount of the successful payment.
     * - payout tells whether the owner's share has already been transferred.
     */
    private function totals(Booking $booking): array
    {
        $amount = round((float) $booking->details->sum('total_price'), 2);
        $rejected = $booking->status === 'rejected';

        // Rejected bookings are never charged, so no commission applies.
        $commission = $rejected
            ? 0.0
            : round((float) $booking->details->sum('commission_amount'), 2);

        // Older rows may have been saved without a commission: use the default rate.
        if (! $rejected && $commission <= 0 && $amount > 0) {
            $commission = round($amount * BookingPriceCalculator::COMMISSION_RATE / 100, 2);
        }

        $commissionRate = $amount > 0
            ? round($commission / $amount * 100, 2)
            : (float) BookingPriceCalculator::COMMISSION_RATE;

        // The relation only holds paid payments (see show()).
        $payment = $booking->payments->first();
        $isPaid = $payment !== null;

        $paymentState = match (true) {
            $rejected => 'not_required',
            $isPaid => 'paid',
            default => 'unpaid',
        };

        $payout = match (true) {
            $rejected || ! $isPaid => 'none',
            filled($payment->stripe_transfer_id) => 'released',
            default => 'held',
        };

        return [
            'amount' => $amount,
            'commission_rate' => $commissionRate,
            'commission' => $commission,
            'owner_earnings' => round($amount - $commission, 2),

            'paid' => $isPaid ? round((float) $payment->amount, 2) : 0.0,

            'guests' => (int) $booking->details->sum(fn (BookingDetail $detail) => $detail->guests),

            'payment_state' => $paymentState,
            'payout' => $payout,
        ];
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
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
                'in:newest,oldest,event_date_asc,event_date_desc,total_desc,total_asc',
            ],
        ]);

        $query = Booking::query()
            ->select('bookings.*')
            ->with(self::RELATIONS)
            ->withCount('details')
            ->withMin('details', 'start_at')
            ->withSum('details', 'total_price')
            ->whereHas('venue', fn (Builder $venue) =>
                $venue->where('owner_id', $owner->getKey())
            );

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date'])) {
            $dayStart = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $filters['date'],
                PublicBookingDetailsRequest::TIMEZONE
            );

            $query->whereHas('details', fn (Builder $details) =>
                $details->where('start_at', '<', $dayStart->addDay()->utc())
                    ->where('end_at', '>', $dayStart->utc())
                    ->when(!empty($filters['space_id']), fn (Builder $detail) =>
                        $detail->where('space_id', $filters['space_id'])
                    )
            );
        } elseif (!empty($filters['space_id'])) {
            $query->whereHas('details', fn (Builder $details) =>
                $details->where('space_id', $filters['space_id'])
            );
        }

        if (!empty($filters['organizer'])) {
            $search = '%' . $filters['organizer'] . '%';

            $query->whereHas('customer', function (Builder $customer) use ($search) {
                $customer->where(function (Builder $nameOrEmail) use ($search) {
                    $nameOrEmail->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            });
        }

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

            default => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id'),
        };

        $bookings = $query->paginate(10)->withQueryString();


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


    private function totals(Booking $booking): array
    {
        $amount = round((float) $booking->details->sum('total_price'), 2);
        $rejected = $booking->status === 'rejected';

        $commission = $rejected
            ? 0.0
            : round((float) $booking->details->sum('commission_amount'), 2);

    
        if (! $rejected && $commission <= 0 && $amount > 0) {
            $commission = round($amount * BookingPriceCalculator::COMMISSION_RATE / 100, 2);
        }

        $commissionRate = $amount > 0
            ? round($commission / $amount * 100, 2)
            : (float) BookingPriceCalculator::COMMISSION_RATE;

      
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

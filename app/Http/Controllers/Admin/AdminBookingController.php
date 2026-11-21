<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicBookingDetailsRequest;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Review;
use App\Models\Venue;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBookingController extends Controller
{
    /** The only booking statuses in the system. */
    public const STATUSES = ['pending', 'confirmed', 'completed', 'rejected'];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'organizer' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,oldest,event_date_asc,event_date_desc,total_desc,total_asc,guests_desc'],
        ]);

        // Guests of a booking = sum of the capacities of the layouts chosen in its details.
        $guestsSubquery = BookingDetail::query()
            ->selectRaw('COALESCE(SUM(COALESCE(space_layouts.capacity, booking_details.guest_count)), 0)')
            ->leftJoin('space_layouts', 'space_layouts.id', '=', 'booking_details.space_layout_id')
            ->whereColumn('booking_details.booking_id', 'bookings.id');

        $query = Booking::query()->select('bookings.*')->with([
            'customer',
            'venue',
            'details.space.venue',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
        ])->withCount('details')
            ->withMin('details', 'start_at')
            ->withSum('details', 'total_price')
            ->addSelect(['guests_total' => $guestsSubquery]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['venue_id'])) {
            $query->where('venue_id', $filters['venue_id']);
        }

        if (! empty($filters['date'])) {
            // Dates are stored in UTC but the day is picked in Amman time.
            $dayStart = CarbonImmutable::createFromFormat('!Y-m-d', $filters['date'], PublicBookingDetailsRequest::TIMEZONE)->utc();

            $query->whereHas('details', fn ($details) => $details
                ->where('start_at', '>=', $dayStart)
                ->where('start_at', '<', $dayStart->addDay()));
        }

        if (! empty($filters['organizer'])) {
            $search = '%'.$filters['organizer'].'%';
            $query->whereHas('customer', function ($customer) use ($search) {
                $customer->where(function ($nameOrEmail) use ($search) {
                    $nameOrEmail->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            });
        }

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'event_date_asc' => $query->orderBy('details_min_start_at')->orderByDesc('id'),
            'event_date_desc' => $query->orderByDesc('details_min_start_at')->orderByDesc('id'),
            'total_desc' => $query->orderByDesc('details_sum_total_price')->orderByDesc('id'),
            'total_asc' => $query->orderBy('details_sum_total_price')->orderByDesc('id'),
            'guests_desc' => $query->orderByDesc('guests_total')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $bookings = $query->paginate(10)
            ->withQueryString();

        // withTrashed(): venues deleted later can still be used to filter their old bookings.
        $venues = Venue::withTrashed()->orderBy('name')->get(['id', 'name']);

        $statuses = self::STATUSES;

        return view('admin.bookings.list', compact('bookings', 'venues', 'filters', 'statuses'));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'customer',
            'venue',
            'details.space.venue',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
            // Every attempt is listed (failed ones help support), but only the
            // successful payment is counted in the totals.
            'payments' => fn ($payments) => $payments->orderBy('id'),
            'review',
        ]);

        $amount = round((float) $booking->details->sum('total_price'), 2);
        $rejected = $booking->status === 'rejected';
        $paidPayment = $booking->payments->firstWhere('status', 'paid');

        $totals = [
            'amount' => $amount,
            // Rejected bookings are never charged, so no commission applies.
            'commission' => $rejected ? 0.0 : round((float) $booking->details->sum('commission_amount'), 2),
            'paid' => $paidPayment ? round((float) $paidPayment->amount, 2) : 0.0,
            'guests' => (int) $booking->details->sum(fn (BookingDetail $detail) => $detail->guests),
            'failed_attempts' => $booking->payments->where('status', 'failed')->count(),
            'payment_state' => match (true) {
                $rejected => 'not_required',
                $paidPayment !== null => 'paid',
                default => 'unpaid',
            },
        ];

        return view('admin.bookings.details', compact('booking', 'totals'));
    }

    public function destroyReview(Booking $booking): RedirectResponse
    {
        $review = Review::where('booking_id', $booking->id)->first();

        if (! $review) {
            return redirect()->route('admin.bookings.show', $booking)
                ->withErrors(['review' => 'This booking has no review to delete.']);
        }

        $review->delete();

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Review deleted successfully.');
    }
}
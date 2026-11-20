<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,rejected'],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'date' => ['nullable', 'date'],
            'organizer' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,oldest,event_date_asc,event_date_desc,total_desc,total_asc,guests_desc'],
        ]);

        $query = Booking::query()->with([
            'customer',
            'venue',
            'details.space.venue',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
        ])->withCount('details')
            ->withMin('details', 'start_at')
            ->withSum('details', 'total_price')
            ->withSum('details', 'guest_count');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['venue_id'])) {
            $query->where('venue_id', $filters['venue_id']);
        }

        if (! empty($filters['date'])) {
            $query->whereHas('details', fn ($details) => $details->whereDate('start_at', $filters['date']));
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
            'guests_desc' => $query->orderByDesc('details_sum_guest_count')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $bookings = $query->paginate(10)
            ->withQueryString();

        // withTrashed(): venues deleted later can still be used to filter their old bookings.
        $venues = Venue::withTrashed()->orderBy('name')->get(['id', 'name']);
        $summary = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
        ];

        return view('admin.bookings.list', compact('bookings', 'venues', 'summary', 'filters'));
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
            'payments',
            'review',
        ]);

        $totals = [
            'amount' => $booking->details->sum('total_price'),
            'commission' => $booking->details->sum('commission_amount'),
            'payments' => $booking->payments->sum('amount'),
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
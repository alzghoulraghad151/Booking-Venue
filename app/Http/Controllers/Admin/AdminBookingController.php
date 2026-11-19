<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Venue;
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
        ]);

        $query = Booking::query()->with([
            'customer',
            'venue',
            'details.space.venue',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
        ])->withCount('details');

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

        $bookings = $query->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $venues = Venue::query()->orderBy('name')->get(['id', 'name']);
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
}

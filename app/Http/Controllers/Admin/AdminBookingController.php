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
        $request->validate([
            'organizer' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,rejected'],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'date' => ['nullable', 'date'],
        ]);

        $query = Booking::with([
            'customer',
            'venue',
            'details.space',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
            'payments',
            'review',
        ]);

        $query->when($request->filled('organizer'), function ($query) use ($request) {
            $search = $request->input('organizer');

            $query->whereHas('customer', function ($customerQuery) use ($search) {
                $customerQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            });
        });

        $query->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->input('status'));
        });

        $query->when($request->filled('venue_id'), function ($query) use ($request) {
            $query->where('venue_id', $request->input('venue_id'));
        });

        $query->when($request->filled('date'), function ($query) use ($request) {
            $query->whereHas('details', function ($detailQuery) use ($request) {
                $detailQuery->whereDate('start_at', $request->input('date'));
            });
        });

        $bookings = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $venues = Venue::orderBy('name')->get();

        return view('admin.bookings.index', compact(
            'bookings',
            'venues'
        ));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'customer',
            'venue',
            'details.space',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
            'payments',
            'review',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }
}

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Space;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $ownerId = $request->user()->getKey();
        $venues = Venue::query()->where('owner_id', $ownerId);
        $venueIds = (clone $venues)->select('id');

        $spaces = Space::query()->whereIn('venue_id', clone $venueIds);
        $bookings = Booking::query()->whereIn('venue_id', clone $venueIds);

        $countDetails = fn (string $status) => BookingDetail::query()
            ->whereIn('booking_id', (clone $bookings)->where('status', $status)->select('id'))
            ->count();

        return view('owner.dashboard', [
            'totalVenues' => $venues->count(),
            'totalSpaces' => (clone $spaces)->count(),
            'pendingDetails' => $countDetails('pending'),
            'confirmedDetails' => $countDetails('confirmed'),
            'recentBookings' => $bookings->with(['customer', 'venue'])
                ->latest()
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'recentSpaces' => $spaces->with('venue')
                ->latest()
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}

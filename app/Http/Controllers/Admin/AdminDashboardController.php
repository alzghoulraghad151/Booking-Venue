<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalVenues = Venue::count();
        $totalBookings = Booking::count();
        $totalOwners = User::where('role', 'venue_owner')->count();
        $pendingVenues = Venue::where('status', 'pending')->count();
        $pendingBookings = Booking::where('status', 'pending')->count();

        $recentBookings = Booking::with(['customer', 'venue'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $venuesAwaitingReview = Venue::where('status', 'pending')
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $totalPaid = DB::table('payments')->where('status', 'paid')->sum('amount');

        $paymentCounts = DB::table('payments')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalCommission = DB::table('booking_details')
            ->join('bookings', 'bookings.id', '=', 'booking_details.booking_id')
            ->whereIn('bookings.status', ['confirmed', 'completed'])
            ->sum('booking_details.commission_amount');

        $averageRating = Review::avg('rating');
        $reviews = Review::with(['booking.customer', 'booking.venue'])
            ->latest()
            ->orderByDesc('id')
            ->simplePaginate(5, ['*'], 'reviews_page');

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalVenues',
            'totalBookings',
            'totalOwners',
            'pendingVenues',
            'pendingBookings',
            'recentBookings',
            'venuesAwaitingReview',
            'totalPaid',
            'paymentCounts',
            'totalCommission',
            'averageRating',
            'reviews',
        ));
    }
}

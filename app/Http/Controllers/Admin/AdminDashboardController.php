<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\CarbonImmutable;
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

        $monthStart = CarbonImmutable::now('Asia/Amman')->startOfMonth();
        $monthEnd = $monthStart->addMonth();
        $monthLabel = $monthStart->format('F Y');

        $monthlyPaid = DB::table('payments')
            ->where('status', 'paid')
            ->where('paid_at', '>=', $monthStart->utc())
            ->where('paid_at', '<', $monthEnd->utc())
            ->sum('amount');

        $paymentCounts = DB::table('payments')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthlyCommission = DB::table('booking_details')
            ->join('bookings', 'bookings.id', '=', 'booking_details.booking_id')
            ->whereIn('bookings.status', ['confirmed', 'completed'])
            ->where('booking_details.start_at', '>=', $monthStart->utc())
            ->where('booking_details.start_at', '<', $monthEnd->utc())
            ->sum('booking_details.commission_amount');

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalVenues',
            'totalBookings',
            'totalOwners',
            'pendingVenues',
            'pendingBookings',
            'recentBookings',
            'venuesAwaitingReview',
            'monthlyPaid',
            'paymentCounts',
            'monthlyCommission',
            'monthLabel',
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;

class AdminDashboardController extends Controller
{
    public function index(){
        $totalUsers = User::count();
        $totalVenues = Venue::count();
        $totalBookings = DB::table('bookings')->count();
        $totalOwners = User::where('role', 'venue_owner')->count();
        $pendingVenues = Venue::where('status', 'pending')->count();
        $pendingBookings = DB::table('bookings')->where('status', 'pending')->count();

        $recentBookings = Booking::with(['customer', 'venue'])
        ->latest()
        ->limit(5)
        ->get();

        $venuesAwaitingReview = Venue::where('status', 'pending')
        ->latest()
        ->limit(5)
        ->get();

        $totalPaid = DB::table('payments')
        ->where('status', 'paid')
        ->sum('amount');

        $paymentCounts = DB::table('payments')
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');


        return view('admin.dashboard', 
        ['totalUsers' => $totalUsers,
        'totalVenues' => $totalVenues,
        'totalBookings' => $totalBookings,
        'totalOwners' => $totalOwners,
        'pendingVenues' => $pendingVenues,
        'pendingBookings' => $pendingBookings,
        'recentBookings' => $recentBookings,
        'venuesAwaitingReview' => $venuesAwaitingReview,
        'totalPaid' => $totalPaid,
        'paymentCounts' => $paymentCounts,]);

    }
}

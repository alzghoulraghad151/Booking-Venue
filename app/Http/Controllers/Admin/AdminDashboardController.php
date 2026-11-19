<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(){
        $totalUsers = User::count();
        $totalVenues = Venue::count();
        $totalBookings = DB::table('bookings')->count();
        $totalOwners = User::where('role', 'venue_owner')->count();
        $pendingVenues = Venue::where('status', 'pending')->count();

        return view('admin.dashboard', 
        ['totalUsers' => $totalUsers,
        'totalVenues' => $totalVenues,
        'totalBookings' => $totalBookings,
        'totalOwners' => $totalOwners,
        'pendingVenues' => $pendingVenues]);

    }
}

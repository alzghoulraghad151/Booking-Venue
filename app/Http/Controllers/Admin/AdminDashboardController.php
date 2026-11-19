<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Venue;

class AdminDashboardController extends Controller
{
    public function index(){
        $totalUsers = User::count();
        $totalVenues = Venue::count();

        return view('admin.dashboard', 
        ['totalUsers' => $totalUsers,
        'totalVenues' => $totalVenues]);

    }
}

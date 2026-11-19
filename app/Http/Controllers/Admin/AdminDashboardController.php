<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index(){
        $totalUsers = User::count();

        return view('admin.dashboard', ['totalUsers' => $totalUsers,]);
    }
}

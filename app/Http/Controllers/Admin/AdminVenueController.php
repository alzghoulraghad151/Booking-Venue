<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\View\View;

class AdminVenueController extends Controller
{
    public function index(): View
    {
        $venues = Venue::with('owner')
            ->latest()
            ->paginate(15);

        return view('admin.venues.index', ['venues' => $venues]);
    }
}
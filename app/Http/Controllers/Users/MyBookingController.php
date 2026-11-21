<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyBookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->where('customer_id', $request->user()->getKey())
            ->with([
                'venue',
                'review',
                'details' => fn ($details) => $details->orderBy('start_at')->orderBy('id'),
                'details.space',
                'details.spaceLayout',
                'details.package',
            ])
            ->latest()
            ->orderByDesc('id')
            ->simplePaginate(10);

        return view('users.bookings.index', ['bookings' => $bookings]);
    }
}

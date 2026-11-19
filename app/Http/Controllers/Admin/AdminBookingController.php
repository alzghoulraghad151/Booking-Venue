<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with([
            'customer',
            'venue',
            'details.space',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
            'payment',
            'review',
        ])->paginate(10);

        return view('admin.bookings.index', compact('bookings'));
    

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
            'payment',
            'review',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }
}

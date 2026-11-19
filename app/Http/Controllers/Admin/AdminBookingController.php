<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with([
            'customer',
            'venue',
            'details.space',
            'details.spaceLayout',
            'details.eventType',
            'details.package',
            'payments',
            'review',
        ]);

      
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });


        $query->when($request->filled('venue_id'), function ($q) use ($request) {
            $q->where('venue_id', $request->venue_id);
        });

   
        $query->when($request->filled('date'), function ($q) use ($request) {
            $q->whereHas('details', function ($detailQuery) use ($request) {
                $detailQuery->whereDate('start_at', $request->date);
            });
        });

      
        $query->when($request->filled('organizer'), function ($q) use ($request) {
            $search = $request->organizer;

            $q->whereHas('customer', function ($customerQuery) use ($search) {
                $customerQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });

        $bookings = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

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
            'payments',
            'review',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }
}

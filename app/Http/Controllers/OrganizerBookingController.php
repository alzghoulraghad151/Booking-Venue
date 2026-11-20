<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerBookingController extends Controller
{
    public function show(Request $request, string $booking): View
    {
        $booking = Booking::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'venue',
                'details.space.images',
                'details.spaceLayout',
                'details.eventType',
                'payments',
                'review',
                'details.package' => fn ($query) => $query
                    ->withTrashed()
                    ->with([
                        'items' => fn ($query) => $query->whereNull('space_id'),
                    ]),
            ])
            ->findOrFail($booking);
             $totalAmount = $booking->details->sum('total_price');
             $paidAmount = $booking->payments->where('status', 'paid')->sum('amount');
             $remainingAmount = max(0, round($totalAmount - $paidAmount, 2));
             
             return view('organizer.bookings.show', compact(
                 'booking',
                 'totalAmount',
                 'paidAmount',
                 'remainingAmount'
             ));
        
    }
}
<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\StripeBookingCheckout;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyBookingController extends Controller
{
    public function index(Request $request, StripeBookingCheckout $checkout): View
    {
        if ($request->query('checkout') === 'submitted') {
            $checkout->reconcileReturnedPayments((int) $request->user()->getKey());
        }

        $bookings = Booking::query()
            ->where('customer_id', $request->user()->getKey())
            ->withExists(['payments as has_paid_payment' => fn ($payments) => $payments->where('status', 'paid')])
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

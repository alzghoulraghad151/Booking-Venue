<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\StripeBookingCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

class StripeCheckoutController extends Controller
{
    public function __invoke(Request $request, Booking $booking, StripeBookingCheckout $checkout): RedirectResponse
    {
        try {
            return redirect()->away($checkout->start($booking, (int) $request->user()->getKey()));
        } catch (ApiErrorException) {
            return to_route('my-bookings.index')->with('error', 'Stripe checkout is temporarily unavailable.');
        }
    }
}

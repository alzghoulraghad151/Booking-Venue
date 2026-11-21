<?php

namespace App\Http\Controllers;

use App\Services\StripeBookingCheckout;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeBookingWebhookController extends Controller
{
    public function __invoke(Request $request, StripeBookingCheckout $checkout): Response
    {
        $secret = config('services.stripe.webhook_secret');
        abort_if(blank($secret), 503);

        try {
            $event = Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature', ''), $secret);
        } catch (UnexpectedValueException | SignatureVerificationException) {
            return response('Invalid Stripe event signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $checkout->recordPaid($event->data->object);
        }

        return response()->noContent();
    }
}

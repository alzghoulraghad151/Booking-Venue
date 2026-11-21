<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeBookingCheckout
{
    public const DEMO_USD_PER_JOD = 1.41;

    public function start(Booking $booking, int $customerId): string
    {
        $key = (string) config('services.stripe.secret');

        if (! str_starts_with($key, 'sk_test_') || blank(config('services.stripe.webhook_secret'))) {
            throw ValidationException::withMessages(['payment' => 'Stripe Sandbox checkout is not configured.']);
        }

        $stripe = new StripeClient($key);

        // Persist the attempt before calling Stripe so a retry can reuse its idempotency key.
        $payment = DB::transaction(function () use ($booking, $customerId): Payment {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->getKey());
            abort_unless((int) $booking->customer_id === $customerId, 404);

            if ($booking->status !== 'confirmed' || $booking->payments()->where('status', 'paid')->exists()) {
                throw ValidationException::withMessages(['payment' => 'This booking is not eligible for payment.']);
            }

            $pending = $booking->payments()->where('status', 'pending')->whereNotNull('stripe_usd_cents')->first();
            if ($pending) {
                return $pending;
            }

            $jod = $booking->details()->sum('total_price');
            $usdCents = (int) round((float) $jod * self::DEMO_USD_PER_JOD * 100);

            if ($usdCents < 50 || $usdCents > 99999999) {
                throw ValidationException::withMessages(['payment' => 'The booking total is outside the supported checkout range.']);
            }

            $payment = $booking->payments()->make([
                'amount' => $jod,
                'payment_method' => 'online',
                'status' => 'pending',
            ]);
            $payment->stripe_usd_cents = $usdCents;
            $payment->save();

            return $payment;
        });

        $url = DB::transaction(function () use ($stripe, $payment): ?string {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            if ($payment->status !== 'pending') {
                return null;
            }

            if ($payment->transaction_reference) {
                $session = $stripe->checkout->sessions->retrieve($payment->transaction_reference);

                if ($session->status === 'expired') {
                    $payment->update(['status' => 'failed']);

                    return null;
                }
            } else {
                // After Stripe's idempotency retention window, reconcile an unknown session before retrying.
                if ($payment->created_at->lt(now()->subHours(23))) {
                    return null;
                }

                $session = $stripe->checkout->sessions->create([
                    'mode' => 'payment',

                    'line_items' => [[
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => (int) $payment->stripe_usd_cents,
                            'product_data' => ['name' => 'Convene booking #'.$payment->booking_id],
                        ],
                    ]],
                    'metadata' => ['convene_payment_id' => (string) $payment->getKey()],
                    'success_url' => route('my-bookings.index', ['checkout' => 'submitted']),
                    'cancel_url' => route('my-bookings.index', ['checkout' => 'cancelled']),
                ], ['idempotency_key' => 'convene-checkout-'.$payment->getKey()]);

                $payment->update(['transaction_reference' => $session->id]);
            }

            return $session->status === 'open' ? $session->url : null;
        });

        if ($url === null) {
            throw ValidationException::withMessages(['payment' => 'This payment session is unavailable. Refresh My Bookings before trying again.']);
        }

        return $url;
    }

    public function recordPaid(Session $session): void
    {
        if ($session->livemode || $session->payment_status !== 'paid' || $session->currency !== 'usd') {
            return;
        }

        $id = $session->metadata->convene_payment_id ?? null;
        if (! is_string($id) || ! ctype_digit($id)) {
            return;
        }

        DB::transaction(function () use ($id, $session): void {
            $payment = Payment::query()->lockForUpdate()->find($id);

            if ($payment?->status === 'pending'
                && $payment->transaction_reference === $session->id
                && (int) $payment->stripe_usd_cents === $session->amount_total) {
                $payment->update(['status' => 'paid', 'paid_at' => now()]);
            }
        });
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ReleaseSandboxBookingPayment extends Command
{
    protected $signature = 'bookings:sandbox-release {booking} {--release : Complete the booking and transfer test funds}';

    protected $description = 'Preview or release the owner share of a completed Sandbox booking';

    public function handle(): int
    {
        $key = (string) config('services.stripe.secret');
        if (! app()->environment('local') || ! str_starts_with($key, 'sk_test_')) {
            $this->error('A local Stripe Sandbox is required.');

            return self::FAILURE;
        }

        try {
            return DB::transaction(fn () => $this->processBooking(new StripeClient($key)));
        } catch (ApiErrorException $exception) {
            $this->error('Stripe rejected the Sandbox request (HTTP '.$exception->getHttpStatus().').');

            return self::FAILURE;
        }
    }

    private function processBooking(StripeClient $stripe): int
    {
        $booking = Booking::query()->lockForUpdate()->findOrFail($this->argument('booking'));
        $payments = $booking->payments()->where('status', 'paid')->whereNotNull('stripe_usd_cents')->get();

        if (! in_array($booking->status, ['confirmed', 'completed'], true) || $payments->count() !== 1) {
            $this->error('The booking must be confirmed or completed and have one paid Stripe payment.');

            return self::FAILURE;
        }

        $payment = $payments->first();
        $ends = $booking->details()->get(['end_at'])->max('end_at');
        $owner = $booking->venue?->owner;
        $destination = (string) config('services.stripe.sandbox_owner_account');

        if (! $ends || ! $owner || $owner->email !== config('services.stripe.sandbox_owner_email')
            || ! str_starts_with($destination, 'acct_')) {
            $this->error('The booking needs an end time and a matching Sandbox owner account.');

            return self::FAILURE;
        }

        $total = (int) $payment->stripe_usd_cents;
        $commission = intdiv($total + 5, 10);
        $share = $total - $commission;

        $this->line(sprintf('Owner: USD %.2f | Platform commission: USD %.2f | Ends: %s UTC',
            $share / 100, $commission / 100, $ends->copy()->utc()->format('Y-m-d H:i')));

        if (! $this->option('release')) {
            return self::SUCCESS;
        }

        if ($ends->isFuture()) {
            $this->error('The booking has not ended yet.');

            return self::FAILURE;
        }

        $session = $stripe->checkout->sessions->retrieve($payment->transaction_reference);
        if ($session->livemode || $session->payment_status !== 'paid' || $session->currency !== 'usd'
            || $session->amount_total !== $total || ($session->metadata->convene_payment_id ?? null) !== (string) $payment->id) {
            $this->error('Stripe payment verification failed.');

            return self::FAILURE;
        }

        $intent = $stripe->paymentIntents->retrieve($session->payment_intent);
        $charge = $stripe->charges->retrieve($intent->latest_charge);
        if (! $charge->paid || $charge->disputed || $charge->amount_refunded > 0) {
            $this->error('The source charge is unpaid, disputed, or refunded.');

            return self::FAILURE;
        }

        $group = $charge->transfer_group ?: 'convene-sandbox-payment-'.$payment->id;
        $existing = $stripe->transfers->all(['transfer_group' => $group, 'limit' => 2]);
        if ($existing->data) {
            $transfer = $existing->data[0];
            if (count($existing->data) !== 1 || $transfer->destination !== $destination
                || $transfer->amount !== $share || $transfer->source_transaction !== $charge->id || $transfer->reversed
                || $transfer->amount_reversed > 0) {
                $this->error('An existing transfer needs reconciliation.');

                return self::FAILURE;
            }
        } else {
            $transfer = $stripe->transfers->create([
                'amount' => $share,
                'currency' => 'usd',
                'destination' => $destination,
                'source_transaction' => $charge->id,
                'transfer_group' => $group,
                'metadata' => ['convene_booking_id' => (string) $booking->id],
            ], ['idempotency_key' => 'convene-sandbox-release-'.$payment->id]);
        }

        $payment->stripe_transfer_id = $transfer->id;
        $payment->save();
        $booking->update(['status' => 'completed']);
        $this->info('Sandbox transfer recorded: '.$transfer->id);

        return self::SUCCESS;
    }
}

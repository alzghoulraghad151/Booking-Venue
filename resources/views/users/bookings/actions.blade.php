<div class="site-booking-actions">
    @if ($booking->has_paid_payment)
        <p>Paid</p>
    @elseif ($booking->status === 'confirmed' && $booking->details->isNotEmpty()
        && str_starts_with((string) config('services.stripe.secret'), 'sk_test_')
        && filled(config('services.stripe.webhook_secret')))
        <form method="POST" action="{{ route('my-bookings.checkout', $booking) }}">
            @csrf
            <button class="site-button" type="submit">Pay in USD · Sandbox</button>
            <small>Demo rate: 1 JOD = {{ \App\Services\StripeBookingCheckout::DEMO_USD_PER_JOD }} USD.</small>
        </form>
    @endif
    @if ($booking->details->isNotEmpty())
        <details>
            <summary>Booking details</summary>
            @foreach ($booking->details as $detail)
                <dl class="site-booking-facts">
                    <dt>Space</dt>
                    <dd>{{ $detail->space?->name ?? 'Unavailable' }}</dd>
                    <dt>Starts</dt>
                    <dd>{{ $detail->start_at?->format('d M Y, H:i') ?? '—' }}</dd>
                    <dt>Ends</dt>
                    <dd>{{ $detail->end_at?->format('d M Y, H:i') ?? '—' }}</dd>
                    <dt>Layout</dt>
                    <dd>{{ $detail->spaceLayout?->name ?? '—' }}</dd>
                    @if ($detail->package)
                        <dt>Package</dt>
                        <dd>{{ $detail->package->name }}</dd>
                    @endif
                    <dt>Guests</dt>
                    <dd>{{ $detail->guest_count }}</dd>
                    <dt>Price</dt>
                    <dd>{{ number_format((float) $detail->total_price, 2) }} JOD</dd>
                </dl>
            @endforeach
        </details>
    @endif

    @if ($booking->status === 'completed' && ! $booking->review)
        @php($showReviewErrors = (string) old('review_booking') === (string) $booking->id)
        <details @if ($showReviewErrors && $errors->any()) open @endif>
            <summary>Add review</summary>
            <form class="site-review-form" action="{{ route('my-bookings.reviews.store', $booking) }}" method="POST">
                @csrf
                <input type="hidden" name="review_booking" value="{{ $booking->id }}">
                <input type="hidden" name="page" value="{{ $page }}">

                <label for="rating-{{ $booking->id }}">Rating</label>
                <select id="rating-{{ $booking->id }}" name="rating" required>
                    <option value="">Select a rating</option>
                    @foreach (range(1, 5) as $rating)
                        <option value="{{ $rating }}" @selected($showReviewErrors && (string) old('rating') === (string) $rating)>{{ $rating }} / 5</option>
                    @endforeach
                </select>
                @if ($showReviewErrors)
                    @error('rating')
                        <p role="alert">{{ $message }}</p>
                    @enderror
                @endif

                <label for="comment-{{ $booking->id }}">Comment <small>(optional)</small></label>
                <textarea id="comment-{{ $booking->id }}" name="comment" rows="3" maxlength="2000">{{ $showReviewErrors ? old('comment') : '' }}</textarea>
                @if ($showReviewErrors)
                    @error('comment')
                        <p role="alert">{{ $message }}</p>
                    @enderror
                @endif

                <button class="site-button" type="submit">Submit review</button>
            </form>
        </details>
    @endif
</div>

@extends('layouts.admin')

@section('title', 'Review details')
@section('hide-layout-heading', '1')

@section('content')
    <div class="admin-record admin-review-detail">
        <header class="admin-record-header">
            <div>
                <h1>Review for booking {{ $review->booking_id }}</h1>
                <p class="admin-description">Read the organizer’s feedback and review the linked booking.</p>
            </div>
            <a class="admin-button admin-button--outline" href="{{ route('admin.reviews.index') }}">Back to reviews</a>
            @if (Route::has('admin.bookings.show') && $review->booking)
                <a class="admin-button admin-button--secondary" href="{{ route('admin.bookings.show', $review->booking) }}">View booking</a>
            @endif
        </header>

        <dl>
            <dt>Organizer</dt>
            <dd>{{ $review->booking?->customer?->name ?? 'Unavailable' }}</dd>
            <dt>Venue</dt>
            <dd>{{ $review->booking?->venue?->name ?? 'Unavailable' }}</dd>
            <dt>Rating</dt>
            <dd>{{ $review->rating }}</dd>
            <dt>Reviewed on</dt>
            <dd>{{ $review->created_at?->format('d M Y') ?? 'Unavailable' }}</dd>
        </dl>

        <section class="dashboard-panel" aria-labelledby="review-comment">
            <h2 id="review-comment">Organizer comment</h2>
            <p class="admin-review-comment">{{ filled($review->comment) ? $review->comment : 'No written comment was provided.' }}</p>
        </section>

        <form data-delete-message="This permanently removes the organizer’s rating and comment. The booking will remain." data-delete-action="Delete" onsubmit="return confirm('Delete this item?')" action="{{ route('admin.reviews.destroy', $review) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="admin-button admin-button--danger" type="submit">Delete review</button>
            </form>
    </div>
@endsection

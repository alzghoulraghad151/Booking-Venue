@extends('layouts.admin')

@section('title', 'Reviews')

@section('content')
    <section id="reviews" class="dashboard-panel" aria-labelledby="reviews-title">
        <header class="dashboard-panel-header">
            <h2 id="reviews-title">Venue Reviews</h2>
            <p class="dashboard-badge">Average rating: {{ $averageRating === null ? 'No ratings yet' : number_format($averageRating, 1) . ' / 5' }}</p>
        </header>
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-labelledby="reviews-title">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th scope="col">Booking #</th>
                        <th scope="col">Organizer</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Comment</th>
                        <th scope="col">Reviewed On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>#{{ $review->booking_id }}</td>
                            <td>{{ $review->booking?->customer?->name ?? '—' }}</td>
                            <td>{{ $review->booking?->venue?->name ?? '—' }}</td>
                            <td>{{ $review->rating }} / 5</td>
                            <td>{{ $review->comment ?? '—' }}</td>
                            <td>{{ $review->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No reviews on this page.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <nav class="dashboard-panel-header dashboard-pagination" aria-label="Review pages">
                @if ($reviews->previousPageUrl())
                    <a href="{{ $reviews->previousPageUrl() }}#reviews">Previous</a>
                @endif
                <span>Page {{ $reviews->currentPage() }}</span>
                @if ($reviews->nextPageUrl())
                    <a href="{{ $reviews->nextPageUrl() }}#reviews">Next</a>
                @endif
            </nav>
        @endif
    </section>
@endsection

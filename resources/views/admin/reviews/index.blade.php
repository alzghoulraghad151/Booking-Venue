@extends('layouts.admin')

@section('title', 'Reviews')
@section('hide-layout-heading', '1')

@section('content')
    <x-admin.list-heading title="Reviews" description="Find organizer feedback and review ratings across venues." />

    @if (session('success'))
        <p role="status">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <ul role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('admin.reviews.index') }}" method="GET" class="admin-review-filters">
        <label for="review-search">Search reviews</label>
        <input id="review-search" type="search" name="search" value="{{ $search }}"
               placeholder="Booking #, organizer, venue, or comment" maxlength="100">
        <label for="review-rating">Rating</label>
        <select id="review-rating" name="rating">
            <option value="">All ratings</option>
            @foreach (range(5, 1) as $value)
                <option value="{{ $value }}" @selected($rating === (string) $value)>{{ $value }} / 5</option>
            @endforeach
        </select>
        <label for="review-sort">Sort by</label>
        <select id="review-sort" name="sort">
            @foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'highest' => 'Highest rating', 'lowest' => 'Lowest rating'] as $value => $label)
                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-button admin-filter-submit">Apply filters</button>
        @if ($search !== '' || $rating !== '' || $sort !== 'newest')
            <a class="admin-button admin-button--text" href="{{ route('admin.reviews.index') }}">Clear filters</a>
        @endif
    </form>

    <section id="reviews" class="dashboard-panel" aria-labelledby="reviews-title">
        <header class="dashboard-panel-header">
            <h2 id="reviews-title">Venue Reviews</h2>
            <p class="dashboard-badge">Overall average: {{ $averageRating === null ? 'No ratings yet' : number_format($averageRating, 1) . ' / 5' }}</p>
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
                        <th scope="col">Reviewed on</th>
                        <th scope="col" class="admin-table-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>#{{ $review->booking_id }}</td>
                            <td>{{ $review->booking?->customer?->name ?? '—' }}</td>
                            <td>{{ $review->booking?->venue?->name ?? '—' }}</td>
                            <td><span class="dashboard-badge">{{ $review->rating }} / 5</span></td>
                            <td>{{ filled($review->comment) ? Str::limit($review->comment, 110) : '—' }}</td>
                            <td>{{ $review->created_at?->format('d M Y') ?? '—' }}</td>
                            <td class="admin-table-actions">
                                <a class="admin-table-icon" href="{{ route('admin.reviews.show', $review) }}" aria-label="View review for booking {{ $review->booking_id }}" title="View">
                                    <x-admin.table-action-icon name="view" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                {{ $search !== '' || $rating !== '' ? 'No reviews match your filters.' : 'No reviews have been submitted yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reviews->onEachSide(1)->links('components.admin.pagination') }}
    </section>
@endsection

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'sort' => ['nullable', 'in:newest,oldest,highest,lowest'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.reviews.index')->withErrors($validator);
        }

        $filters = $validator->validated();
        $search = trim($filters['search'] ?? '');
        $rating = (string) ($filters['rating'] ?? '');
        $sort = $filters['sort'] ?? 'newest';

        $query = Review::with(['booking.customer', 'booking.venue']);

        if ($search !== '') {
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';

            $query->where(function ($matches) use ($search, $term) {
                $matches->whereRaw("comment LIKE ? ESCAPE '!'", [$term])
                    ->orWhereHas('booking.customer', fn ($customer) => $customer->whereRaw("name LIKE ? ESCAPE '!'", [$term]))
                    ->orWhereHas('booking.venue', fn ($venue) => $venue->whereRaw("name LIKE ? ESCAPE '!'", [$term]));

                $bookingId = ltrim($search, '#');
                if (ctype_digit($bookingId) && filter_var($bookingId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
                    $matches->orWhere('booking_id', (int) $bookingId);
                }
            });
        }

        if ($rating !== '') {
            $query->where('rating', (int) $rating);
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'highest' => $query->orderByDesc('rating')->latest()->orderByDesc('id'),
            'lowest' => $query->orderBy('rating')->latest()->orderByDesc('id'),
            default => $query->latest()->orderByDesc('id'),
        };

        $reviews = $query->paginate(10)->withQueryString();
        $averageRating = Review::avg('rating');

        return view('admin.reviews.index', compact('reviews', 'averageRating', 'search', 'rating', 'sort'));
    }

    public function show(Review $review): View
    {
        $review->load(['booking.customer', 'booking.venue']);

        return view('admin.reviews.show', compact('review'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(): View
    {
        $averageRating = Review::avg('rating');
        $reviews = Review::with(['booking.customer', 'booking.venue'])
            ->latest()
            ->orderByDesc('id')
            ->simplePaginate(5);

        return view('admin.reviews.index', compact('averageRating', 'reviews'));
    }
}

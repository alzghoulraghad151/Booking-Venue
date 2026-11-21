<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingReviewController extends Controller
{
    public function store(Request $request, int $booking): RedirectResponse
    {
        return $this->saveReview($request, $booking, editing: false);
    }

    public function update(Request $request, int $booking): RedirectResponse
    {
        return $this->saveReview($request, $booking, editing: true);
    }

    private function saveReview(Request $request, int $booking, bool $editing): RedirectResponse
    {
        $customerId = $request->user()->getKey();

        try {
            DB::transaction(function () use ($request, $booking, $customerId, $editing) {
                $ownedBooking = Booking::query()
                    ->where('customer_id', $customerId)
                    ->lockForUpdate()
                    ->findOrFail($booking);

                if ($ownedBooking->status !== 'completed'
                    || ! $ownedBooking->payments()->where('status', 'paid')->exists()) {
                    throw ValidationException::withMessages([
                        'review' => 'You can review this booking once it is completed and paid.',
                    ]);
                }

                $existingReview = $ownedBooking->review()->first();

                if ($editing && ! $existingReview) {
                    abort(404);
                }

                if (! $editing && $existingReview) {
                    throw ValidationException::withMessages([
                        'review' => 'You have already reviewed this booking.',
                    ]);
                }

                $review = $request->validate([
                    'rating' => ['required', 'integer', 'between:1,5'],
                    'comment' => ['nullable', 'string', 'max:2000'],
                ]);

                if ($existingReview) {
                    $existingReview->update($review);
                } else {
                    $ownedBooking->review()->create($review);
                }
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'review' => 'You have already reviewed this booking.',
            ]);
        }

        $page = $request->integer('page');

        return to_route('my-bookings.index', $page > 1 ? ['page' => $page] : [])
            ->with('success', $editing ? 'Your review was updated.' : 'Your review was submitted.');
    }
}

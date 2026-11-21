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
        $customerId = $request->user()->getKey();

        try {
            DB::transaction(function () use ($request, $booking, $customerId) {
                $ownedBooking = Booking::query()
                    ->where('customer_id', $customerId)
                    ->lockForUpdate()
                    ->findOrFail($booking);

                if ($ownedBooking->status !== 'completed') {
                    throw ValidationException::withMessages([
                        'review' => 'You can review this booking once it is completed.',
                    ]);
                }

                if ($ownedBooking->review()->exists()) {
                    throw ValidationException::withMessages([
                        'review' => 'You have already reviewed this booking.',
                    ]);
                }

                $review = $request->validate([
                    'rating' => ['required', 'integer', 'between:1,5'],
                    'comment' => ['nullable', 'string', 'max:2000'],
                ]);

                $ownedBooking->review()->create($review);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'review' => 'You have already reviewed this booking.',
            ]);
        }

        $page = $request->integer('page');

        return to_route('my-bookings.index', $page > 1 ? ['page' => $page] : [])
            ->with('success', 'Your review was submitted.');
    }
}

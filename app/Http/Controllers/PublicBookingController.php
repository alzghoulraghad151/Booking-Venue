<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicBookingDetailsRequest;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Space;
use App\Services\BookingPriceCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
  
    public function create(PublicBookingDetailsRequest $request, Space $space): View
    {
        $selection = $request->resolved();
        $space = $selection['space']->loadMissing(['venue', 'images']); 

        $image = $space->images->sortByDesc('is_primary')->first();
        $imageUrl = null;

        if ($image) {
            $imageUrl = str_starts_with($image->image_url, 'http')
                ? $image->image_url
                : Storage::disk('public')->url($image->image_url);
        }

        return view('public.bookings.create', [
            'space' => $space,
            'venue' => $space->venue,
            'eventType' => $selection['eventType'],
            'layout' => $selection['layout'],
            'package' => $selection['package'],
            'imageUrl' => $imageUrl,
            'minDate' => CarbonImmutable::now(PublicBookingDetailsRequest::TIMEZONE)->toDateString(),
        ]);
    }

    public function store(PublicBookingDetailsRequest $request, Space $space, BookingPriceCalculator $calculator): RedirectResponse
    {
        $selection = $request->resolved();

        $space = $selection['space'];
        $layout = $selection['layout'];
        $eventType = $selection['eventType'];
        $package = $selection['package'];
        $guestCount = $selection['guestCount']; // capacity of the selected layout

        // Stored in the app timezone (UTC), like the rest of the project.
        $start = $selection['start']->setTimezone('UTC');
        $end = $selection['end']->setTimezone('UTC');

        // The price is ALWAYS recalculated here, never taken from the form.
        $price = $calculator->calculate($space, $package, $start, $end);

        // Non-blocking: the owner takes the final decision when confirming.
        $overlapsConfirmed = BookingDetail::query()
            ->where('space_id', $space->getKey())
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->whereHas('booking', fn ($booking) => $booking->where('status', 'confirmed'))
            ->exists();

        DB::transaction(function () use ($request, $space, $layout, $eventType, $package, $guestCount, $start, $end, $price): void {
            $booking = Booking::query()->create([
                'customer_id' => $request->user()->getKey(),
                'venue_id' => $space->venue_id,
                'status' => 'pending',
            ]);

            $booking->details()->create([
                'space_id' => $space->getKey(),
                'space_layout_id' => $layout->getKey(),
                'event_type_id' => $eventType->getKey(),
                'package_id' => $package?->getKey(),
                'start_at' => $start,
                'end_at' => $end,
                'guest_count' => $guestCount,
                'pricing_type' => $price['pricing_type'],
                'unit_price' => $price['unit_price'],
                'total_price' => $price['total'],
                'commission_rate' => $price['commission_rate'],
                'commission_amount' => $price['commission_amount'],
            ]);
        });

        // No Payment row here: payment happens at checkout, after the owner confirms.
        $redirect = redirect()->route('my-bookings.index')
            ->with('success', 'Your booking request was sent. The venue owner will review it.');

        if ($overlapsConfirmed) {
            $redirect->with('warning', 'This time overlaps an already confirmed booking, so the owner may reject your request.');
        }

        return $redirect;
    }
}
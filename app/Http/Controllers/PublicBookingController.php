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
        $space = $selection['space']->loadMissing(['venue.images', 'images']);

        // Same order as the space details page (primary first), then the venue's photos as a fallback.
        // The first image that resolves to a usable URL wins; null shows a neutral placeholder.
        $imageUrl = $space->images
            ->sortBy([['is_primary', 'desc'], ['id', 'asc']])
            ->concat($space->venue?->images->sortBy([['is_primary', 'desc'], ['id', 'asc']]) ?? [])
            ->map(fn ($image) => $this->resolveImageUrl($image->image_url))
            ->first(fn ($url) => $url !== null);

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

    /**
     * Absolute URLs (Cloudinary) are used as stored. Relative paths are checked on disk first,
     * and built with asset() so the host and port follow the current request, not APP_URL.
     */
    private function resolveImageUrl(?string $value): ?string
    {
        $path = trim((string) $value);

        if ($path === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $relative = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;

        if (Storage::disk('public')->exists($relative)) {
            return asset('storage/'.$relative);
        }

        return is_file(public_path($path)) ? asset($path) : null;
    }
}
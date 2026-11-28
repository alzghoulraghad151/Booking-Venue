<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Space;
use Illuminate\Support\Facades\DB;


class AvailabilityService
{
    public function confirm(int $bookingId, int $ownerId): ?string
    {
        return DB::transaction(function () use ($bookingId, $ownerId): ?string {
            $booking = Booking::query()->lockForUpdate()->findOrFail($bookingId);
            $booking->load(['venue', 'details.space', 'details.spaceLayout', 'details.package']);

            $venue = $booking->venue;

          
            abort_unless($venue && (int) $venue->owner_id === $ownerId, 404);

            if ($booking->status !== 'pending') {
                return 'Only pending bookings can be confirmed.';
            }

            if ($venue->trashed() || $venue->status !== 'approved' || ! $venue->is_active_by_owner) {
                return 'The venue is not approved or is currently unavailable.';
            }

            if ($booking->details->isEmpty()) {
                return 'This booking has no details to confirm.';
            }

           
            Space::query()
                ->whereIn('id', $booking->details->pluck('space_id')->unique())
                ->lockForUpdate()
                ->get();

            foreach ($booking->details as $detail) {
                $failure = $this->validateDetail($booking, $detail);

                if ($failure !== null) {
                    return $failure;
                }
            }

            $booking->update(['status' => 'confirmed']);

            return null;
        });
    }

    private function validateDetail(Booking $booking, BookingDetail $detail): ?string
    {
        $space = $detail->space;
        $layout = $detail->spaceLayout;
        $package = $detail->package;
        $name = $space?->name ?? 'space #'.$detail->space_id;

        if (! $space || (int) $space->venue_id !== (int) $booking->venue_id) {
            return "The space for this booking does not belong to the venue.";
        }

        if ($space->status !== 'active') {
            return "{$name} is not available for booking ({$space->status}).";
        }

        if ($detail->end_at <= $detail->start_at) {
            return "{$name} has an invalid time range.";
        }

        if (! $layout || (int) $layout->space_id !== (int) $space->id) {
            return "The selected layout does not belong to {$name}.";
        }

        if ($detail->guest_count !== null && ($detail->guest_count > $layout->capacity || $detail->guest_count > $space->capacity)) {
            return "The guest count exceeds the capacity of {$name} / its layout.";
        }

        if (! $layout->eventTypes()->whereKey($detail->event_type_id)->exists()) {
            return "The selected layout of {$name} does not support this event type.";
        }

        if ($detail->pricing_type === 'package') {
            if (! $package) {
                return "{$name} is booked with a package that no longer exists.";
            }

            if (! $package->is_active) {
                return "The package '{$package->name}' is not active.";
            }

            if ((int) $package->owner_id !== (int) $booking->venue->owner_id) {
                return "The package '{$package->name}' does not belong to this venue owner.";
            }

            if (($detail->guest_count !== null && ($detail->guest_count < $package->min_guests || $detail->guest_count > $package->max_guests))
                || ($detail->guest_count === null && $package->min_guests > min($space->capacity, $layout->capacity))) {
                return "The guest count is outside the range of the package '{$package->name}'.";
            }

            if ($package->eventTypes()->exists()
                && ! $package->eventTypes()->whereKey($detail->event_type_id)->exists()) {
                return "The package '{$package->name}' does not support this event type.";
            }
        }

        $conflict = BookingDetail::query()
            ->where('space_id', $detail->space_id)
            ->where('booking_id', '!=', $booking->getKey())
            ->where('start_at', '<', $detail->end_at)
            ->where('end_at', '>', $detail->start_at)
            ->whereHas('booking', fn ($query) => $query->where('status', 'confirmed'))
            ->exists();

        if ($conflict) {
            return "{$name} is already booked (confirmed) for the requested time.";
        }

        return null;
    }
}

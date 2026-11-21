<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Space;
use Carbon\CarbonInterface;

/**
 * Single source of truth for the price of one booking detail.
 *
 * Without a package:
 *   total = space.hourly_rate x hours
 * With a package (fixed price, the space is included in it):
 *   total = package price      (the space is NOT charged on top)
 */
class BookingPriceCalculator
{
    /** Same platform commission used in OwnerBookingController. */
    public const COMMISSION_RATE = 10;

    /**
     * @return array{
     *     hours: float,
     *     hourly_rate: float,
     *     space_price: float,
     *     package_price: float,
     *     total: float,
     *     pricing_type: string,
     *     unit_price: float,
     *     commission_rate: int,
     *     commission_amount: float
     * }
     */
    public function calculate(Space $space, ?Package $package, CarbonInterface $start, CarbonInterface $end): array
    {
        $seconds = max(0, $end->getTimestamp() - $start->getTimestamp());
        $hours = $seconds / 3600;

        $hourlyRate = (float) $space->hourly_rate;
        $packagePrice = $package ? round((float) $package->price, 2) : 0.0;

        // The space is included in the package price, so it is only charged without a package.
        $spacePrice = $package ? 0.0 : round($hourlyRate * $hours, 2);
        $total = $package ? $packagePrice : $spacePrice;

        return [
            'hours' => round($hours, 2),
            'hourly_rate' => $hourlyRate,
            'space_price' => $spacePrice,
            'package_price' => $packagePrice,
            'total' => $total,
            'pricing_type' => $package ? 'package' : 'hourly',
            'unit_price' => $package ? $packagePrice : $hourlyRate,
            'commission_rate' => self::COMMISSION_RATE,
            'commission_amount' => round($total * self::COMMISSION_RATE / 100, 2),
        ];
    }
}
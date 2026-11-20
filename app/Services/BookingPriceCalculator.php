<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Space;
use Carbon\CarbonInterface;

/**
 * Single source of truth for the price of one booking detail.
 *
 *   space price = space.hourly_rate x hours
 *   total       = space price + package price (if a package is selected)
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
        $spacePrice = round($hourlyRate * $hours, 2);
        $packagePrice = $package ? round((float) $package->price, 2) : 0.0;
        $total = round($spacePrice + $packagePrice, 2);

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

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
 * With a package (the package price already includes 1 hour of the space):
 *   total = package price + space.hourly_rate x (hours - 1)
 *   (never less than the package price: if hours <= 1 there are no extra hours)
 */
class BookingPriceCalculator
{
    /** Same platform commission used in OwnerBookingController. */
    public const COMMISSION_RATE = 10;

    /** Hours of the space that are already included in a package price. */
    public const PACKAGE_INCLUDED_HOURS = 1;

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

        // With a package, the first hour of the space is included in the package price.
        $billableHours = $package ? max(0.0, $hours - self::PACKAGE_INCLUDED_HOURS) : $hours;
        $spacePrice = round($hourlyRate * $billableHours, 2);
        $total = round($spacePrice + $packagePrice, 2);

        return [
            'hours' => round($hours, 2),
            'billable_hours' => round($billableHours, 2),
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
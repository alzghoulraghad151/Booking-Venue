<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Space;
use Carbon\CarbonInterface;

class BookingPriceCalculator
{
   
    public const COMMISSION_RATE = 10;

   
    public const PACKAGE_INCLUDED_HOURS = 1;

  
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
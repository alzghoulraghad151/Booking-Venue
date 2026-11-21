<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $addressesByCity = [
            'Amman' => [
                'amman',
                'Abdoun, Amman',
                'Downtown, Amman',
                'Jabal Amman, Amman',
                'Khalda, Amman',
                'Shmeisani, Amman',
                'Sweifieh, Amman',
                'Tabarbour, Amman',
                'Wadi Al Seer, Amman',
            ],
            'Ajloun' => ['Ajloun City, Ajloun'],
            'Aqaba' => ['City Centre, Aqaba', 'Marina, Aqaba'],
            'Balqa' => ['Fuheis, Balqa', 'Salt, Balqa'],
            'Irbid' => ['Al Husn, Irbid', 'University Street, Irbid'],
            'Jerash' => ['Jerash City, Jerash'],
            'Karak' => ['City Centre, Karak'],
            'Madaba' => ['City Centre, Madaba'],
            'Maan' => ['Wadi Musa, Maan'],
            'Zarqa' => ['New Zarqa, Zarqa'],
        ];

        DB::transaction(function () use ($addressesByCity): void {
            foreach ($addressesByCity as $city => $addresses) {
                DB::table('venues')
                    ->whereIn('address', $addresses)
                    ->update(['address' => $city]);
            }
        });
    }

    public function down(): void
    {
        // Several old addresses map to one city, so the original values cannot be recovered.
    }
};
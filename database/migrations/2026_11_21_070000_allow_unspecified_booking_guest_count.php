<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_details', function (Blueprint $table): void {
            $table->unsignedInteger('guest_count')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('booking_details')->whereNull('guest_count')->exists()) {
            throw new RuntimeException('Assign actual guest counts before reverting this migration.');
        }

        Schema::table('booking_details', function (Blueprint $table): void {
            $table->unsignedInteger('guest_count')->nullable(false)->change();
        });
    }
};

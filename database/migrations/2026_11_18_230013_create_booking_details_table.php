<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('space_id')->constrained('spaces');
            $table->foreignId('space_layout_id')->constrained('space_layouts');
            $table->foreignId('event_type_id')->constrained('event_types');
            $table->foreignId('package_id')->nullable()->constrained('packages');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedInteger('guest_count');
            $table->enum('pricing_type', ['hourly', 'package']);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->timestamps();
            $table->index(['space_id', 'start_at', 'end_at'], 'booking_details_space_time_index');
        });

        try {
            DB::statement('ALTER TABLE booking_details ADD CONSTRAINT booking_details_valid_range CHECK (end_at > start_at)');
        } catch (\Throwable $exception) {
            Schema::dropIfExists('booking_details');

            throw $exception;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};

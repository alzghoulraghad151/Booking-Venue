<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        try {
            DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_valid_range CHECK (rating BETWEEN 1 AND 5)');
        } catch (\Throwable $exception) {
            Schema::dropIfExists('reviews');

            throw $exception;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

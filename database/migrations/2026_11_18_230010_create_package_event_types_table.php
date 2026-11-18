<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_event_types', function (Blueprint $table) {
            $table->foreignId('package_id')->constrained('packages');
            $table->foreignId('event_type_id')->constrained('event_types');
            $table->unique(['package_id', 'event_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_event_types');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layout_event_types', function (Blueprint $table) {
            $table->foreignId('space_layout_id')->constrained('space_layouts');
            $table->foreignId('event_type_id')->constrained('event_types');
            $table->unique(['space_layout_id', 'event_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layout_event_types');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages');
            $table->foreignId('space_id')->nullable()->constrained('spaces');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unique(['package_id', 'space_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
    }
};

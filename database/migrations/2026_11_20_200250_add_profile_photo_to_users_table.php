<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Services\CloudinaryImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
             $table->text('profile_photo_url')->nullable();
             $table->string('profile_photo_public_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
              $table->dropColumn([
            'profile_photo_url',
            'profile_photo_public_id',
        ]);


        });
    }
};

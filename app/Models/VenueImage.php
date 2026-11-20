<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueImage extends Model
{
   protected $fillable = ['image_url', 'cloudinary_public_id', 'is_primary'];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
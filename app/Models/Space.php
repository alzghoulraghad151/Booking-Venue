<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    public function spaceLayout(){
        return $this->hasmany(SpaceLayout::class);
    }
     public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
      public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}

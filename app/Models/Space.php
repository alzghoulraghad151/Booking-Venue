<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Space extends Model
{
    protected $fillable = [
        'name',
        'description',
        'capacity',
        'hourly_rate',
        'status',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(SpaceImage::class);
    }

    public function spaceLayouts(): HasMany
    {
        return $this->hasMany(SpaceLayout::class);

    }
    public function packages(): BelongsToMany
    {
       return $this->belongsToMany(
           Package::class,
           'package_items',
           'space_id',
           'package_id'
       );
   }
     public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Venue extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'address'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VenueImage::class);
    }

    public function spaces(): HasMany
{
    return $this->hasMany(Space::class);
}

  public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Booking::class);
    }

    protected function casts(): array
    {
        return [
            'is_active_by_owner' => 'boolean',
        ];
    }
}



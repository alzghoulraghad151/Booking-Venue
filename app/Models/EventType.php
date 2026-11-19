<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    public const APPROVED_NAMES = [
        'Dinner',
        'Networking',
        'Workshop / Seminar',
        'Corporate Conference',
        'Team Building',
    ];

    public $timestamps = false;

    protected $fillable = ['name', 'description'];

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
}

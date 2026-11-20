<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_event_types');
    }

    public function spaceLayouts(): BelongsToMany
    {
        return $this->belongsToMany(SpaceLayout::class, 'layout_event_types');
    }

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
}

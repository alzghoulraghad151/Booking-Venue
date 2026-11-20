<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpaceLayout extends Model
{
    protected $fillable = [
        'space_id',
        'name',
        'capacity',
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function eventTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            EventType::class,
            'layout_event_types',
            'space_layout_id',
            'event_type_id'
        );
     public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
    public function eventTypes(){
        return $this->belongsToMany(EventTybe::class,'layout_event_types');
    }
}
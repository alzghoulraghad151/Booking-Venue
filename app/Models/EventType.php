<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_event_types');
    }

    public function spaceLayouts()
    {
        return $this->belongsToMany(SpaceLayout::class, 'layout_event_types');
    }
}
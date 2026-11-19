<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use SoftDeletes;
     protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'min_guests',
        'max_guests',
        'is_active',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function items()
{
    return $this->hasMany(PackageItem::class);
}
public function eventTypes()
{
    return $this->belongsToMany(EventType::class, 'package_event_types');
}
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SpaceLayout extends Model
{
    use SoftDeletes;
    protected $fillable = ['space_id','name','capacity'];

    public function space(){
        return $this->belongsTo(Space::class);
    }
    public function eventTypes(){
        return $this->belongsToMany(EventType::class,'layout_event_types');
    }
}

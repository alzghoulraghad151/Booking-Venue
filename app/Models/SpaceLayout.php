<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceLayout extends Model
{
    protected $fillable = ['space_id','name','capacity'];

    public function space(){
        return $this->belongsTo(Space::class);
    }
    public function eventTypes(){
        return $this->belongsToMany(EventTybe::class,'layout_event_types');
    }
}

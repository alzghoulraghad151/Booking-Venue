<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    public function spaceLayout(){
        return $this->hasmany(SpaceLayout::class);
    }
}

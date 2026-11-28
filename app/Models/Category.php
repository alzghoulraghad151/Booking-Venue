<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Category extends Model
{
    
   protected $fillabel =['name'];


     public function Users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageItem extends Model
{
     public $timestamps = false;

    protected $fillable = [
        'space_id',
        'name',
        'description',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}

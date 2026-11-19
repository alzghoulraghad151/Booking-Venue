<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'venue_id',
        'status',
    ];

   
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }


    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }


    public function details(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

  public function payments(): HasMany
{
    return $this->hasMany(Payment::class);
}

   
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}

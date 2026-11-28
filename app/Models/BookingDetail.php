<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDetail extends Model
{
    protected $fillable = [
        'booking_id',
        'space_id',
        'space_layout_id',
        'event_type_id',
        'package_id',
        'start_at',
        'end_at',
        'guest_count',
        'pricing_type',
        'unit_price',
        'total_price',
        'commission_rate',
        'commission_amount',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

   
    public function getGuestsAttribute(): int
    {
        return (int) ($this->spaceLayout?->capacity ?? $this->guest_count ?? 0);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function spaceLayout(): BelongsTo
    {
        return $this->belongsTo(SpaceLayout::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
<?php

namespace App\Models;

use App\RoomType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Room extends Model
{
    protected $casts = [
        'type' => RoomType::class,
    ];

    protected $fillable = [
        'building_id',
        'name',
        'type',
    ];

    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function bookings(): MorphMany{
        return $this->morphMany(Booking::class, 'bookable');
    }
}

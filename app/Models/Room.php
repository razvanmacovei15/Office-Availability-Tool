<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Room extends Model
{
    protected $fillable = [
        'building_id',
        'name',
    ];

    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function bookings(): MorphMany{
        return $this->morphMany(Booking::class, 'bookable');
    }
}

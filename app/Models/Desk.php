<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Desk extends Model
{
    protected $fillable = [
        'room_id',
        'name',
    ];

    public function room(){
        return $this->belongsTo(Room::class);
    }

    public function bookings(): MorphMany{
        return $this->morphMany(Booking::class, 'bookable');
    }
}

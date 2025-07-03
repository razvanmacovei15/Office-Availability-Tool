<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desk extends Model
{
    protected $fillable = [
        'room_id',
        'name',
    ];

    public function room(){
        return $this->belongsTo(Room::class);
    }

    public function bookings(){
        return $this->morphMany(Booking::class, 'bookable');
    }
}

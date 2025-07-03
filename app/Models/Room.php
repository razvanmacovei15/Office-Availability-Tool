<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'building_id',
        'name',
    ];

    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function rooms(){
        return $this->hasMany(Room::class);
    }

    public function bookings(){
        return $this->morphMany(Booking::class, 'bookable');
    }
}

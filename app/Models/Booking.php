<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'booking_date',
        'bookable_id',
        'bookable_type',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function bookable() {
        return $this->morphTo();
    }
}

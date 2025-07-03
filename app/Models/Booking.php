<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'booking_date',
        'bookable_id',
        'bookable_type',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function bookable(): MorphTo {
        return $this->morphTo();
    }

    public static function isBooked(string $bookableType, int $bookableId, string|Carbon $bookingDate): bool
    {
        return self::where('bookable_type', $bookableType)
            ->where('bookable_id', $bookableId)
            ->whereDate('booking_date', Carbon::parse($bookingDate))
            ->exists();
    }

    public static function getAvailableDesks(int $roomId, string|Carbon $bookingDate): \Illuminate\Support\Collection
    {
        $bookingDate = Carbon::parse($bookingDate);

        // Check if the room itself is booked
        $roomBooked = self::where('bookable_type', Room::class)
            ->where('bookable_id', $roomId)
            ->whereDate('booking_date', $bookingDate)
            ->exists();

        if ($roomBooked) {
            // If the room is booked, no desks are available
            return collect();
        }

        // Otherwise, check for individually booked desks
        $bookedDeskIds = self::where('bookable_type', Desk::class)
            ->whereDate('booking_date', $bookingDate)
            ->pluck('bookable_id');

        return Desk::where('room_id', $roomId)
            ->whereNotIn('id', $bookedDeskIds)
            ->get();
    }


    public static function getAvailableRooms(int $buildingId, string|Carbon $bookingDate): Collection
    {
        $bookedRoomIds = self::where('bookable_type', Room::class)
            ->whereDate('booking_date', Carbon::parse($bookingDate))
            ->pluck('bookable_id');

        return Room::where('building_id', $buildingId)
            ->whereNotIn('id', $bookedRoomIds)
            ->get();
    }

    public function getBookable(): ?Model
    {
        return $this->bookable;
    }

    public function getRoom(): ?Room
    {
        return match (true) {
            $this->bookable instanceof Desk => $this->bookable->room,
            $this->bookable instanceof Room => $this->bookable,
            default => null,
        };
    }

    public function getBuilding(): ?Building
    {
        return $this->getRoom()?->building;
    }

}

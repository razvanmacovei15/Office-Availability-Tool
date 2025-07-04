<?php

namespace App\Filament\Resources\UserBookingResource\Pages;

use App\Filament\Resources\UserBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserBooking extends CreateRecord
{
    protected static string $resource = UserBookingResource::class;
}

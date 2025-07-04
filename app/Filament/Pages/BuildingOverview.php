<?php

namespace App\Filament\Pages;

use App\Models\Building;
use App\Models\Room;
use App\Models\Desk;
use App\Models\Booking;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class BuildingOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Offices';
    protected static ?string $title = 'Offices';
    protected static ?string $navigationGroup = 'Bookings';

    protected static string $view = 'filament.pages.building-overview';
    public $filterDate;

    public $step = 1;
    public $selectedBuildingId = null;
    public $selectedRoomId = null;
    public $selectedDeskId = null;
    public function clearFilters()
    {
        $this->filterDate = null;
        $this->selectedDeskId = null;
    }
    public function getBuildings()
    {
        return Building::all();
    }

    public function getRooms()
    {
        // Always return all rooms for the building, regardless of availability
        return Room::where('building_id', $this->selectedBuildingId)->get();
    }

    public function getDesks()
    {
        if (!$this->filterDate) {
            return Desk::where('room_id', $this->selectedRoomId)->get();
        }

        // Get desks with availability for the selected date
        return Booking::getAvailableDesks($this->selectedRoomId, $this->filterDate);
    }

    public function getAllDesks()
    {
        return Desk::where('room_id', $this->selectedRoomId)->get();
    }

    public function isDeskBooked($deskId)
    {
        if (!$this->filterDate) {
            return false;
        }

        return Booking::isBooked(Desk::class, $deskId, $this->filterDate);
    }

    public function getRoomAvailabilityCount($roomId)
    {
        if (!$this->filterDate) {
            return Desk::where('room_id', $roomId)->count();
        }

        return Booking::getAvailableDesks($roomId, $this->filterDate)->count();
    }

        public function getUserBookingForDate()
    {
        if (!$this->filterDate) {
            return null;
        }
        
        return Booking::where('user_id', auth()->id())
            ->whereDate('booking_date', $this->filterDate)
            ->first();
    }

    public function getUserBookedDeskId()
    {
        if (!$this->filterDate) {
            return null;
        }
        
        $booking = Booking::where('user_id', auth()->id())
            ->where('bookable_type', Desk::class)
            ->whereDate('booking_date', $this->filterDate)
            ->first();
            
        return $booking ? $booking->bookable_id : null;
    }

    public function isUserDeskBooked($deskId)
    {
        if (!$this->filterDate) {
            return false;
        }
        
        return $this->getUserBookedDeskId() == $deskId;
    }

    public function getSelectedDeskId()
    {
        return $this->selectedDeskId;
    }

    public function isRoomCompletelyBooked($roomId)
    {
        if (!$this->filterDate) {
            return false;
        }

        // Check if the room itself is booked
        $roomBooked = Booking::where('bookable_type', Room::class)
            ->where('bookable_id', $roomId)
            ->whereDate('booking_date', $this->filterDate)
            ->exists();

        if ($roomBooked) {
            return true;
        }

        // Check if all desks in the room are booked
        $totalDesks = Desk::where('room_id', $roomId)->count();
        $availableDesks = Booking::getAvailableDesks($roomId, $this->filterDate)->count();

        return $availableDesks === 0;
    }

    public function isRoomAvailableForBooking($roomId)
    {
        if (!$this->filterDate) {
            return false;
        }

        // Room is available if it's not completely booked
        return !$this->isAnyDeskBookedInRoom($roomId);
    }

    public function isAdminOrManager()
    {
        return auth()->user()->hasAnyRole(['admin', 'manager']);
    }

    public function isAnyDeskBookedInRoom($roomId, $date = null)
    {
        // Use provided date or fall back to filterDate
        $checkDate = $date ?? $this->filterDate;

        if (!$checkDate) {
            return false;
        }

        // First check if the room itself is booked
        $roomBooked = Booking::where('bookable_type', Room::class)
            ->where('bookable_id', $roomId)
            ->whereDate('booking_date', $checkDate)
            ->exists();

        if ($roomBooked) {
            return true; // If room is booked, all desks are considered booked
        }

        // Check if any desk in the room is booked
        $deskIds = Desk::where('room_id', $roomId)->pluck('id');
        $anyDeskBooked = Booking::where('bookable_type', Desk::class)
            ->whereIn('bookable_id', $deskIds)
            ->whereDate('booking_date', $checkDate)
            ->exists();

        return $anyDeskBooked;
    }

    public function selectBuilding($buildingId)
    {
        $this->selectedBuildingId = $buildingId;
        $this->step = 2;
    }

    public function selectRoom($roomId){
        $this->selectedRoomId = $roomId;
        $this->step = 3;
    }

    public function bookRoom($roomId)
    {
        if (!$this->isAdminOrManager()) {
            Notification::make()
                ->danger()
                ->title('Access Denied')
                ->body('Only administrators and managers can book entire rooms.')
                ->send();
            return;
        }

        if (!$this->filterDate) {
            Notification::make()
                ->warning()
                ->title('Date Required')
                ->body('Please select a date first')
                ->send();
            return;
        }

        // Check if room is available for booking
        if (!$this->isRoomAvailableForBooking($roomId)) {
            Notification::make()
                ->danger()
                ->title('Room Unavailable')
                ->body('This room is not available for booking on the selected date.')
                ->send();
            return;
        }

        // Check if user already has a booking for this date
        $existingBooking = Booking::where('user_id', auth()->id())
            ->whereDate('booking_date', $this->filterDate)
            ->first();

        if ($existingBooking) {
            Notification::make()
                ->danger()
                ->title('Booking Limit Exceeded')
                ->body('You already have a booking for this date. Only one booking per day is allowed.')
                ->send();
            return;
        }

        try {
            // Create the room booking
            Booking::create([
                'user_id' => auth()->id(),
                'booking_date' => $this->filterDate,
                'bookable_id' => $roomId,
                'bookable_type' => Room::class,
            ]);

            $room = Room::find($roomId);
            Notification::make()
                ->success()
                ->title('Room Booked Successfully')
                ->body("Room '{$room->name}' has been booked for the entire day.")
                ->send();

            // Reset selections and reload
            $this->selectedDeskId = null;
            $this->selectedRoomId = null;
            $this->step = 2;

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Booking Failed')
                ->body('An error occurred while booking the room. Please try again.')
                ->send();
        }
    }

    public function goBackToRooms()
    {
        $this->step = 2;
        $this->selectedRoomId = null;
        $this->selectedDeskId = null;
    }

    public function selectDesk($deskId)
    {
        if (!$this->filterDate) {
            Notification::make()
                ->warning()
                ->title('Date Required')
                ->body('Please select a date first')
                ->send();
            return;
        }

        // Check if user already has a booking for this date
        $userBookedDeskId = $this->getUserBookedDeskId();
        if ($userBookedDeskId && $userBookedDeskId != $deskId) {
            Notification::make()
                ->warning()
                ->title('Booking Limit Exceeded')
                ->body('You already have a desk booked for this date. You can only book one desk per day.')
                ->send();
            return;
        }

        if ($this->isDeskBooked($deskId)) {
            Notification::make()
                ->danger()
                ->title('Desk Unavailable')
                ->body('This desk is already booked for the selected date')
                ->send();
            return;
        }

        $this->selectedDeskId = $deskId;
        
        // Show different notification based on whether it's their own booking or a new selection
        if ($userBookedDeskId == $deskId) {
            Notification::make()
                ->info()
                ->title('Your Booking Selected')
                ->body('You have selected your own booking. You can manage it from the sidebar.')
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('Desk Selected')
                ->body('Desk selected! You can now proceed with booking.')
                ->send();
        }
    }

    public function bookDesk()
    {
        if (!$this->filterDate) {
            Notification::make()
                ->warning()
                ->title('Date Required')
                ->body('Please select a date first')
                ->send();
            return;
        }

        if (!$this->selectedDeskId) {
            Notification::make()
                ->warning()
                ->title('Desk Required')
                ->body('Please select a desk first')
                ->send();
            return;
        }

        // Check if user already has a booking for this date
        $existingBooking = Booking::where('user_id', auth()->id())
            ->whereDate('booking_date', $this->filterDate)
            ->first();

        if ($existingBooking) {
            Notification::make()
                ->danger()
                ->title('Booking Limit Exceeded')
                ->body('You already have a booking for this date. Only one booking per day is allowed.')
                ->send();
            return;
        }

        // Check if desk is still available (double-check)
        if ($this->isDeskBooked($this->selectedDeskId)) {
            Notification::make()
                ->danger()
                ->title('Desk No Longer Available')
                ->body('This desk has been booked by someone else. Please select another desk.')
                ->send();
            return;
        }

        try {
            // Create the booking
            Booking::create([
                'user_id' => auth()->id(),
                'booking_date' => $this->filterDate,
                'bookable_id' => $this->selectedDeskId,
                'bookable_type' => Desk::class,
            ]);

            Notification::make()
                ->success()
                ->title('Booking Successful')
                ->body('Your desk has been booked successfully!')
                ->send();

            // Reset selections and reload
            $this->selectedDeskId = null;
            // $this->selectedRoomId = null;
            $this->step = 3;

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Booking Failed')
                ->body('An error occurred while booking. Please try again.')
                ->send();
        }
    }

    public function deleteBooking()
    {
        if (!$this->filterDate) {
            Notification::make()
                ->warning()
                ->title('Date Required')
                ->body('Please select a date first')
                ->send();
            return;
        }

        // Find the user's booking for the selected date
        $booking = Booking::where('user_id', auth()->id())
            ->whereDate('booking_date', $this->filterDate)
            ->first();

        if (!$booking) {
            Notification::make()
                ->warning()
                ->title('No Booking Found')
                ->body('You don\'t have a booking for this date.')
                ->send();
            return;
        }

        try {
            // Get booking details for the notification
            $bookable = $booking->bookable;
            $bookingDetails = '';

            if ($bookable instanceof Desk) {
                $bookingDetails = "Desk: {$bookable->name} in Room: {$bookable->room->name}";
            } elseif ($bookable instanceof Room) {
                $bookingDetails = "Room: {$bookable->name}";
            }

            // Delete the booking
            $booking->delete();

            Notification::make()
                ->success()
                ->title('Booking Deleted')
                ->body("Your booking for {$bookingDetails} has been successfully deleted.")
                ->send();

            // Reset selections if we're on step 3
            if ($this->step === 3) {
                $this->selectedDeskId = null;
            }

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Deletion Failed')
                ->body('An error occurred while deleting your booking. Please try again.')
                ->send();
        }
    }

    public function resetSteps()
    {
        $this->step = 1;
        $this->selectedBuildingId = null;
        $this->selectedRoomId = null;
        $this->selectedDeskId = null;
    }

    public function searchAvailability()
    {
        if (!$this->filterDate) {
            Notification::make()
                ->warning()
                ->title('Date Required')
                ->body('Please select a date first')
                ->send();
            return;
        }

        if ($this->step < 3) {
            Notification::make()
                ->info()
                ->title('Selection Required')
                ->body('Please select a building and room first to view desk availability')
                ->send();
            return;
        }

        // Refresh the page to show updated availability
        Notification::make()
            ->success()
            ->title('Availability Updated')
            ->body('Availability updated for ' . $this->filterDate)
            ->send();
    }
}

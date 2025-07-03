<?php

namespace App\Filament\Pages;

use App\Models\Building;
use App\Models\Room;
use Filament\Pages\Page;

class BuildingOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Offices';
    protected static ?string $title = 'Offices';
    protected static ?string $navigationGroup = 'Booking';

    protected static string $view = 'filament.pages.building-overview';
    public $filterDate;

    public $step = 1;
    public $selectedBuildingId = null;
    public $selectedRoomId = null;
    public function clearFilters()
    {
        $this->filterDate = null;
    }
    public function getBuildings()
    {
        return Building::all();
    }

    public function getRooms()
    {
        return Room::where('building_id', $this->selectedBuildingId)->get();
    }

    public function getDesks()
    {
        return \App\Models\Desk::where('room_id', $this->selectedRoomId)->get();
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

    public function resetSteps()
    {
        $this->step = 1;
        $this->selectedBuildingId = null;
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Building;
use Filament\Pages\Page;

class BuildingOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Offices';
    protected static ?string $title = 'Offices';
    protected static ?int $navigationSort = 1; // Optional: controls sidebar order

    protected static string $view = 'filament.pages.building-overview';

    public function getBuildings()
    {
        return Building::all();
    }
}

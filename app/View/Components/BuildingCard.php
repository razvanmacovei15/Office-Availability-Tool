<?php

namespace App\View\Components;

use App\Models\Building;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BuildingCard extends Component
{
    public Building $building;
    /**
     * Create a new component instance.
     */
    public function __construct(Building $building)
    {
        $this->building = $building;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.building-card');
    }
}

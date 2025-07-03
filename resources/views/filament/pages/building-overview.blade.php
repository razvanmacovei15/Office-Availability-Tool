<x-filament::page>
    <div class="-mx-8"> {{-- removes the horizontal padding constraint --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; padding: 1.5rem; width: 100%;">
            @foreach ($this->getBuildings() as $building)
                <x-building-card :building="$building" />
            @endforeach
        </div>
    </div>
</x-filament::page>

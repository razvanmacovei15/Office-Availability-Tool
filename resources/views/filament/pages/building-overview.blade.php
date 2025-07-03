<x-filament::page>
    <div class="flex flex-row gap-6">
        <div class="flex-1 gap-4">


            {{-- Step Tracker --}}
            <div class="mb-4">
                <div class="flex space-x-2 text-sm gap-1.5">
                    <span @class(['font-bold' => $step === 1])>Step 1: Choose Building</span>

                    @if ($step >= 2)
                        <span>→</span>
                        <span @class(['font-bold' => $step === 2])>Step 2: Select a Room</span>
                    @endif

                    @if ($step >= 3)
                        <span>→</span>
                        <span @class(['font-bold' => $step === 3])>Step 3: Select a Desk</span>
                    @endif
                </div>
            </div>

            {{-- Warning Message --}}
            @if(empty($filterDate))
                <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium pl-2">Please select a date to view availability</span>
                    </div>
                </div>
            @endif

            {{-- Step 1: Building Grid --}}
            @if ($step === 1)
                <div style="display: grid;
                 grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                 gap: 1.5rem;
                 padding: 1.5rem;
                 width: 100%;">
                    @foreach ($this->getBuildings() as $building)
                        <div wire:click="selectBuilding({{ $building->id }})" style="cursor: pointer;">
                            <x-building-card :building="$building" />
                        </div>
                    @endforeach
                </div>

            {{-- Step 2: Rooms List --}}
            @elseif ($step === 2)
                <div class="mb-4">
                    <button wire:click="resetSteps" class="text-sm text-blue-500 hover:underline flex items-center bg-gray-200 rounded-xl py-2 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to buildings
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach ($this->getRooms() as $room)
                        <div wire:click="selectRoom({{ $room->id }})" style="cursor: pointer;">
                            <x-room-card :room="$room" />
                        </div>
                    @endforeach
                </div>

            {{-- Step 3: Desks Grid --}}
            @elseif ($step === 3)
                <div class="flex flex-row gap-2">
                    <button wire:click="resetSteps" class="text-sm text-blue-500 hover:underline flex items-center bg-gray-200 rounded-xl py-2 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to buildings
                    </button>

                    <button wire:click="$set('step', 2)" class="text-sm text-blue-500 hover:underline bg-gray-200 rounded-xl py-2 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to rooms
                    </button>
                </div>

                <div class="p-4 bg-gray-100 rounded" style="width: 800px; height: 500px; margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 16px; align-content: start;">
                    @foreach ($this->getDesks() as $desk)
                        <div style="
                            width: 100px;
                            height: 60px;
                            background-color: #4ade80;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 12px;
                            color: white;
                            border-radius: 4px;
                        ">
                            {{ $desk->name }}
                        </div>
                    @endforeach
                </div>

            @endif
        </div>

        {{-- Right side: the sidebar --}}
        <div class="w-64 bg-gray-100 rounded p-4">
            <h3 class="text-lg font-bold mb-2">Filters</h3>

            <div class="mb-4">
                <label for="filterDate" class="block text-sm font-medium text-gray-700">Date:</label>
                <input
                    wire:model="filterDate"
                    type="date"
                    id="filterDate"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                >
            </div>

            <div>
                <button wire:click="clearFilters" class="text-sm text-blue-500 hover:underline">
                    Clear filters
                </button>
            </div>
        </div>
    </div>
</x-filament::page>

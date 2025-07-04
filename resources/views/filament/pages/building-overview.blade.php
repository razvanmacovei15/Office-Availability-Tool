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
            @if(!$filterDate)
                <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded"
                     style="display: flex; justify-content: start; align-items: center; text-align: center;">

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span style="margin-left: 10px" class="font-medium pl-2">Please select a date to view availability</span>
                    </div>

                </div>
            @else

            {{-- Selection Summary --}}
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span style="margin-left: 10px" class="font-medium">Viewing availability for: {{ $filterDate }}</span>
                    </div>
                    @if($selectedBuildingId)
                        <div class="mt-2 text-sm">
                            Building: {{ \App\Models\Building::find($selectedBuildingId)?->name }}
                        </div>
                    @endif
                    @if($selectedRoomId && $step === 3)
                        <div class="mt-1 text-sm">
                            Room: {{ \App\Models\Room::find($selectedRoomId)?->name }}
                        </div>
                    @endif
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
                    <button wire:click="resetSteps" class="text-sm text-primary-500 hover:underline flex items-center bg-gray-200 rounded-xl py-2 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to buildings
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach ($this->getRooms() as $room)
                        @php
                            $isRoomCompletelyBooked = $this->isRoomCompletelyBooked($room->id);
                            $isAdminOrManager = $this->isAdminOrManager();
                            $isRoomAvailable = $this->isRoomAvailableForBooking($room->id);
                        @endphp

                        <div class="relative">
                            <div wire:click="selectRoom({{ $room->id }})" style="cursor: pointer;">
                                <x-room-card :room="$room">
                                    <div class="mt-2">

                                    </div>
                                </x-room-card>
                                @if($filterDate)
                                    <div class="text-sm text-gray-600">
                                        Available desks: {{ $this->getRoomAvailabilityCount($room->id) }}
                                        @if($isRoomCompletelyBooked)
                                            <span class="text-red-600 font-semibold"> - Room Fully Booked</span>
                                        @endif
                                    </div>
                                @endif
                                @if($isAdminOrManager && $filterDate && $isRoomAvailable)
                                    <div class="mt-2">
                                        <button
                                            wire:click.stop="bookRoom({{ $room->id }})"
                                            class="w-full bg-primary-500 hover:bg-purple-600 text-white font-medium py-2 px-4 rounded transition-colors duration-200"
                                        >
                                            Book Entire Room
                                        </button>
                                    </div>
                                @endif
                            </div>


                        </div>
                    @endforeach
                </div>

            {{-- Step 3: Desks Grid --}}
            @elseif ($step === 3)
                <div class="flex flex-row gap-2">
                    <button wire:click="resetSteps" class="text-sm text-primary-500 hover:underline flex items-center bg-gray-200 rounded-xl py-2 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to buildings
                    </button>

                    <button wire:click="goBackToRooms" class="text-sm text-primary-500 hover:underline bg-gray-200 rounded-xl py-2 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to rooms
                    </button>
                </div>

                <div class="p-4 bg-gray-100 rounded"
                     style="width: 100%;
                     height: 500px;
                     margin-top: 10px;
                     display: grid;
                     grid-template-columns: repeat(3, 1fr);
                     grid-template-rows: repeat(3, 1fr);
                     gap: 8px;
                     align-items: center;
                     justify-items: center;">
                                        @foreach ($this->getAllDesks() as $index => $desk)
                        @php
                            $isRoomCompletelyBooked = $this->isRoomCompletelyBooked($this->selectedRoomId);
                            $isBooked = $this->isDeskBooked($desk->id) || $isRoomCompletelyBooked;
                            $isUserBooked = $this->isUserDeskBooked($desk->id);
                            $isSelected = $this->getSelectedDeskId() == $desk->id;

                            // Determine background color based on booking status
                            if ($isUserBooked) {
                                $backgroundColor = '#f97316'; // Orange for user's booking
                                $hoverColor = '#ea580c';
                            } elseif ($isBooked) {
                                $backgroundColor = '#ef4444'; // Red for other bookings
                                $hoverColor = '#dc2626';
                            } else {
                                $backgroundColor = '#4ade80'; // Green for available
                                $hoverColor = '#22c55e';
                            }

                            $borderColor = $isSelected ? '#3b82f6' : 'transparent';
                            $borderWidth = $isSelected ? '3px' : '0px';
                        @endphp
                        <div style="
                            width: 100%;
                            height: 100%;
                            background-color: {{ $backgroundColor }};
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 12px;
                            color: white;
                            border-radius: 4px;
                            border: {{ $borderWidth }} solid {{ $borderColor }};
                            cursor: {{ ($isBooked && !$isUserBooked) ? 'not-allowed' : 'pointer' }};
                            transition: all 0.2s ease;
                            box-shadow: {{ $isSelected ? '0 0 8px rgba(59, 130, 246, 0.5)' : 'none' }};
                        "
                        @if(!$isBooked || $isUserBooked)
                            wire:click="selectDesk({{ $desk->id }})"
                        @endif
                        onmouseover="this.style.backgroundColor='{{ $hoverColor }}'"
                        onmouseout="this.style.backgroundColor='{{ $backgroundColor }}'">
                            {{ $desk->name }}
                        </div>
                    @endforeach

                    {{-- Fill remaining grid slots with empty spaces --}}
                    @for ($i = count($this->getAllDesks()); $i < 9; $i++)
                        <div style="
                            width: 100%;
                            height: 100%;
                            background-color: #e5e7eb;
                            border: 2px dashed #d1d5db;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 12px;
                            color: #6b7280;
                            border-radius: 4px;
                        ">
                            Empty
                        </div>
                    @endfor
                </div>

            @endif
        </div>

        {{-- Right side: the sidebar --}}
        <div class="w-64 bg-gray-100 rounded p-4" style="width: 250px">
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
                <button wire:click="clearFilters" class="text-sm text-primary-500 hover:underline">
                    Clear filters
                </button>
            </div>

                <div class="mt-4">
            <button
                wire:click="searchAvailability"
                class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-4 rounded transition-colors duration-200"
            >
                Search for Availability
            </button>
        </div>

        @if($filterDate)
            @php
                $userBooking = $this->getUserBookingForDate();
            @endphp

            @if($userBooking)
                <div style="margin-top: 20px" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <div class="text-sm text-yellow-800">
                        <strong>Your Booking:</strong><br>
                        You already have a booking for this date.
                    </div>
                    <div class="mt-2">
                        <button
                            wire:click="deleteBooking"
                            class="w-full bg-primary-600 hover:bg-red-600 text-white font-medium py-2 px-4 rounded transition-colors duration-200"
                        >
                            Delete Your Booking
                        </button>
                    </div>
                </div>
            @else
                @if($this->getSelectedDeskId() && $step === 3)
                    <div style="margin-top: 20px">
                        <button
                            wire:click="bookDesk"
                            class="w-full bg-primary-600 hover:bg-green-600 text-white font-medium py-2 px-4 rounded transition-colors duration-200"
                        >
                            Book Selected Desk
                        </button>
                    </div>
                @endif
            @endif
        @endif
        </div>
    </div>
</x-filament::page>

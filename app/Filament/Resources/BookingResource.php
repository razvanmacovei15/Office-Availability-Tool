<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Desk;
use App\Models\Room;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\MorphToColumn;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationGroup = 'Admin Tools';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. USER SELECT
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->reactive(),

                // 2. BOOKING DATE
                Forms\Components\DatePicker::make('booking_date')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('building_id', null);
                        $set('room_id', null);
                        $set('bookable_id', null);
                    }),

                // 4. BOOKING TYPE (role-based options)
                Forms\Components\Select::make('bookable_type')
                    ->label('Booking Type')
                    ->options(function (callable $get) {
                        $user = User::find($get('user_id'));
                        if (!$user) return [];

                        if ($user->hasRole(['admin', 'manager'])) {
                            return [
                                Desk::class => 'Desk',
                                Room::class => 'Entire Room',
                            ];
                        }

                        return [
                            Desk::class => 'Desk',
                        ];
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('room_id', null)),

                Forms\Components\Select::make('building_id')
                    ->label('Building')
                    ->options(fn () => Building::all()->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(function (callable $set, $state, $record) {
                        if (! $record) {
                            return;
                        }

                        // Figure out the building_id from the bookable relation
                        $buildingId = match (true) {
                            $record->bookable instanceof \App\Models\Desk => $record->bookable->room->building_id ?? null,
                            $record->bookable instanceof \App\Models\Room => $record->bookable->building_id ?? null,
                            default => null,
                        };

                        $set('building_id', $buildingId);
                    })
                    ->afterStateUpdated(function (callable $set) {
                        $set('room_id', null);
                        $set('bookable_id', null);
                    }),



                // 5. ROOM SELECT (only available rooms for the date & building)
                Forms\Components\Select::make('room_id')
                    ->label('Room')
                    ->options(function (callable $get) {
                        $buildingId = $get('building_id');
                        $bookingDate = $get('booking_date');
                        $bookableType = $get('bookable_type');
                        $selectedRoomId = $get('room_id');

                        $options = [];

                        if (!$buildingId || !$bookingDate) {
                            if (!empty($selectedRoomId)) {
                                return \App\Models\Room::where('id', $selectedRoomId)->pluck('name', 'id')->toArray();
                            }

                            return [];
                        }

                        if ($bookableType === \App\Models\Room::class) {
                            $options = Booking::getAvailableRooms($buildingId, $bookingDate)
                                ->pluck('name', 'id')
                                ->toArray();

                            // Add the booked room back into the list
                            if (!empty($selectedRoomId)) {
                                $currentRoom = \App\Models\Room::where('id', $selectedRoomId)->pluck('name', 'id')->toArray();
                                $options = array_replace($options, $currentRoom);
                            }

                            return $options;
                        }

                        // Otherwise return all rooms in the building
                        return Room::where('building_id', $buildingId)->pluck('name', 'id');
                    })
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(function (callable $set, $state, $record) {
                        if (! $record) {
                            return;
                        }

                        $roomId = match (true) {
                            $record->bookable instanceof \App\Models\Desk => $record->bookable->room->id ?? null,
                            $record->bookable instanceof \App\Models\Room => $record->bookable->id ?? null,
                            default => null,
                        };

                        $set('room_id', $roomId);
                    })
                    ->afterStateUpdated(fn (callable $set) => $set('bookable_id', null))
                    ->disabled(fn (callable $get) => blank($get('building_id')) || blank($get('booking_date'))),

                Forms\Components\Select::make('bookable_id')
                    ->label('Desk or Room')
                    ->options(function (callable $get) {
                        $roomId = $get('room_id');
                        $bookingDate = $get('booking_date');
                        $type = $get('bookable_type');
                        $selectedBookableId = $get('bookable_id');

                        $options = [];

                        if ($type === Desk::class) {
                            // Start with available desks
                            $options = Booking::getAvailableDesks($roomId, $bookingDate)
                                ->pluck('name', 'id')
                                ->toArray();

                            // Add the currently booked desk (even if it's already booked)
                            if ($selectedBookableId) {
                                $currentDesk = \App\Models\Desk::where('id', $selectedBookableId)->pluck('name', 'id')->toArray();
                                $options = array_replace($options, $currentDesk);
                            }

                            return $options;
                        }

                        if ($type === Room::class) {
                            return Room::where('id', $roomId)->pluck('name', 'id');
                        }

                        return [];
                    })
                    ->required()
                    ->afterStateHydrated(function (callable $set, $state, $record) {
                        if (!$record) {
                            return;
                        }

                        $set('bookable_id', $record->bookable?->id);
                    })
                    ->disabled(fn (callable $get) => blank($get('room_id')) || blank($get('booking_date'))),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User'),

                Tables\Columns\TextColumn::make('bookable_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                Tables\Columns\TextColumn::make('bookable.name')
                    ->label('Booked Item')
                    ->formatStateUsing(fn ($state, $record) => $record->bookable?->name ?? '-'),

                Tables\Columns\TextColumn::make('room_name')
                    ->label('Room')
                    ->getStateUsing(fn ($record) => $record->getRoom()?->name ?? '-'),

                Tables\Columns\TextColumn::make('building_name')
                    ->label('Building')
                    ->getStateUsing(fn ($record) => $record->getBuilding()?->name ?? '-'),

                Tables\Columns\TextColumn::make('booking_date')->date(),

                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        // If the bookable_type was not selected (normal user), default it to Desk::class
        if (!isset($data['bookable_type'])) {
            $data['bookable_type'] = \App\Models\Desk::class;
        }

        return $data;
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // Same for updates
        if (!isset($data['bookable_type'])) {
            $data['bookable_type'] = \App\Models\Desk::class;
        }

        return $data;
    }

    public static function canViewAny(): bool {
        return Auth::user()?->hasRole('admin');
    }

}

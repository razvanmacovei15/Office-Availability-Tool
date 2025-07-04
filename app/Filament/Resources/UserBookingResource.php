<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserBookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserBookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationLabel = 'My Bookings';
    protected static ?string $navigationGroup = 'Bookings';
    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return Booking::query()
            ->where('user_id', auth()->id());
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
//                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUserBookings::route('/'),
//            'create' => Pages\CreateUserBooking::route('/create'),
//            'edit' => Pages\EditUserBooking::route('/{record}/edit'),
        ];
    }
}

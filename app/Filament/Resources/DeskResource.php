<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeskResource\Pages;
use App\Filament\Resources\DeskResource\RelationManagers;
use App\Models\Building;
use App\Models\Desk;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DeskResource extends Resource
{
    protected static ?string $model = Desk::class;
    protected static ?string $navigationGroup = 'Admin Tools';

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('building_id')
                    ->label('Building')
                    ->options(Building::whereHas('rooms')
                        ->pluck('name', 'id'))
                    ->required()
                    ->live(), // This will re-render the form when changed

                Select::make('room_id')
                    ->label('Room')
                    ->options(fn (callable $get) => Room::where('building_id', $get('building_id'))->pluck('name', 'id'))
                    ->required()
                    ->disabled(fn (callable $get) => !$get('building_id'))
                    ->reactive(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('room.name')->label('Room')->sortable()->searchable(),
                TextColumn::make('room.building.name')->label('Building')->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->label('Building')
                    ->options(
                        Building::whereHas('rooms')->pluck('name', 'id')
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('room', function ($roomQuery) use ($data) {
                            $roomQuery->where('building_id', $data['value']);
                        });
                    }),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDesks::route('/'),
            'create' => Pages\CreateDesk::route('/create'),
            'edit' => Pages\EditDesk::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool {
        return Auth::user()?->hasRole('admin');
    }
}

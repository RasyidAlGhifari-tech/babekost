<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Bangunan / Lokasi';

    // Hari 5: CRUD bangunan (nama, alamat, segmen putra/putri).
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nama bangunan')
                ->required()
                ->maxLength(255),

            Textarea::make('address')
                ->label('Alamat')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Select::make('segment')
                ->label('Segmen penghuni')
                ->options([
                    'putra' => 'Putra',
                    'putri' => 'Putri',
                ])
                ->required()
                ->helperText('Satu bangunan hanya untuk satu segmen — tidak ada kos campur.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('address')->label('Alamat')->limit(40),
                TextColumn::make('segment')->label('Segmen')->badge(),
                TextColumn::make('rooms_count')->label('Jumlah kamar')->counts('rooms'),
                TextColumn::make('deleted_at')->label('Dihapus')->dateTime()->since()->placeholder('-'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}

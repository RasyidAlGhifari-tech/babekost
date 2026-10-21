<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use App\Services\ImageResizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\UploadedFile;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'Kamar';

    // Hari 6: CRUD kamar dengan harga tiga paket, fasilitas, dan upload
    // foto yang otomatis diperkecil.
    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('location_id')
                ->label('Bangunan')
                ->relationship('location', 'name')
                ->required()
                ->searchable(),

            TextInput::make('number')
                ->label('Nomor kamar')
                ->required()
                ->maxLength(50)
                ->helperText('Unik per bangunan. Boleh dipakai ulang kalau kamar lama sudah dihapus.'),

            TextInput::make('type')
                ->label('Tipe kamar')
                ->maxLength(100),

            TagsInput::make('facilities')
                ->label('Fasilitas')
                ->placeholder('Ketik lalu Enter, mis. AC, kamar mandi dalam')
                ->columnSpanFull(),

            TextInput::make('price_monthly')
                ->label('Harga bulanan')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            TextInput::make('price_quarterly')
                ->label('Harga 3 bulan')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            TextInput::make('price_yearly')
                ->label('Harga tahunan')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            Select::make('special_status')
                ->label('Status khusus')
                ->options([
                    'normal' => 'Normal',
                    'perbaikan' => 'Sedang diperbaiki',
                    'nonaktif' => 'Nonaktif',
                ])
                ->default('normal')
                ->required()
                ->helperText('Ketersediaan sewa tetap dihitung dari rentang tanggal rental, bukan dari sini.'),

            FileUpload::make('photos')
                ->label('Foto kamar')
                ->multiple()
                ->image()
                ->reorderable()
                ->panelLayout('grid')
                ->saveUploadedFileUsing(
                    fn (UploadedFile $file): string => ImageResizer::storeResized($file, 'rooms')
                )
                ->columnSpanFull()
                ->helperText('Foto otomatis diperkecil & dikonversi ke WebP saat diunggah.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photos.0.path')
                    ->label('Foto')
                    ->disk('public'),
                TextColumn::make('location.name')->label('Bangunan')->sortable(),
                TextColumn::make('number')->label('Nomor')->searchable(),
                TextColumn::make('price_monthly')->label('Harga/bulan')->money('IDR'),
                TextColumn::make('special_status')->label('Status')->badge(),
                TextColumn::make('deleted_at')->label('Dihapus')->dateTime()->since()->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('location_id')->label('Bangunan')->relationship('location', 'name'),
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}

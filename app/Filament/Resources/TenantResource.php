<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
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

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Penyewa';

    /**
     * Hari 7: CRUD penyewa. Dipakai admin untuk dua kasus (bagian 4.5):
     *  - mendaftarkan penghuni lama / walk-in yang belum punya akun.
     *  - melihat/mengedit profil penyewa yang sudah daftar sendiri.
     *
     * Saat CREATE, form menampilkan field akun (nama/email/password)
     * karena User belum ada. Saat EDIT, akun sudah ada, jadi field itu
     * diganti jadi tampilan read-only (ganti email/password penyewa
     * bukan kasus CRUD biasa — di luar cakupan Fase 2).
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Akun baru')
                ->visible(fn (?Tenant $record): bool => $record === null)
                ->schema([
                    TextInput::make('user_name')->label('Nama')->required()->maxLength(255),
                    TextInput::make('user_email')->label('Email')->email()->required()
                        ->unique('users', 'email'),
                    TextInput::make('user_password')->label('Password awal')->password()
                        ->required()->minLength(8)
                        ->helperText('Penyewa bisa menggantinya lewat "lupa password".'),
                ]),

            Section::make('Akun')
                ->visible(fn (?Tenant $record): bool => $record !== null)
                ->schema([
                    Placeholder::make('user.name')->label('Nama')
                        ->content(fn (?Tenant $record): string => $record?->user->name ?? '-'),
                    Placeholder::make('user.email')->label('Email')
                        ->content(fn (?Tenant $record): string => $record?->user->email ?? '-'),
                ]),

            Section::make('Profil penyewa')
                ->schema([
                    TextInput::make('phone')->label('Nomor HP')->required()->maxLength(30),
                    TextInput::make('emergency_contact')->label('Kontak darurat')->required()->maxLength(255),
                    TextInput::make('campus_or_job')->label('Kampus / pekerjaan')->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Nama')->searchable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('No HP'),
                TextColumn::make('rentals_count')->label('Jumlah rental')->counts('rentals'),
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}

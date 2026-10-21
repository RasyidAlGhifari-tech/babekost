<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\RoomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoom extends CreateRecord
{
    protected static string $resource = RoomResource::class;

    /**
     * 'photos' bukan kolom di tabel rooms (ada tabel room_photos
     * terpisah, lihat migration Hari 6), jadi dikeluarkan dulu dari
     * $data sebelum Room::create(), lalu disimpan manual sebagai baris
     * room_photos setelah record kamarnya ada.
     */
    protected array $photoPaths = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->photoPaths = $data['photos'] ?? [];
        unset($data['photos']);

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->photoPaths as $index => $path) {
            $this->record->photos()->create([
                'path' => $path,
                'display_order' => $index,
            ]);
        }
    }
}

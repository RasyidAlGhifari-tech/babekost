<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Concerns\HandlesOptimisticLocking;
use App\Filament\Resources\RoomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoom extends EditRecord
{
    use HandlesOptimisticLocking;

    protected static string $resource = RoomResource::class;

    protected array $photoPaths = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // Isi field 'photos' dari tabel room_photos saat form dibuka —
    // bukan kolom asli di model, jadi tidak ter-fill otomatis.
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['photos'] = $this->record->photos()->pluck('path')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->photoPaths = $data['photos'] ?? [];
        unset($data['photos']);

        return $data;
    }

    // Full-sync sederhana: hapus semua baris lama, buat ulang sesuai
    // urutan yang ada di form. Cukup untuk skala CRUD admin kos ini.
    protected function afterSave(): void
    {
        $this->record->photos()->delete();

        foreach ($this->photoPaths as $index => $path) {
            $this->record->photos()->create([
                'path' => $path,
                'display_order' => $index,
            ]);
        }
    }
}

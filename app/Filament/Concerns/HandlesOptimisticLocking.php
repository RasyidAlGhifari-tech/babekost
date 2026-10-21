<?php

namespace App\Filament\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait reusable untuk SEMUA halaman Edit Filament (Hari 7).
 * Mengimplementasikan "optimistic locking sederhana" dari bagian 3.1:
 *  - Saat form edit dibuka, nilai updated_at disimpan (lihat mount()).
 *  - Saat disimpan, nilai itu dibandingkan dengan data terbaru di DB.
 *  - Jika berbeda, perubahan DITOLAK dengan pesan persis seperti di
 *    dokumen, dan proses save dihentikan (halt).
 *
 * Dipakai cukup dengan `use HandlesOptimisticLocking;` di class halaman
 * Edit mana pun — tidak perlu field tambahan di form.
 *
 * Catatan teknis: $loadedUpdatedAt adalah public property Livewire biasa
 * (bukan bagian dari form schema), jadi otomatis ikut ter-serialize di
 * antara request tanpa perlu hidden input di form.
 */
trait HandlesOptimisticLocking
{
    public ?string $loadedUpdatedAt = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->loadedUpdatedAt = $this->record->updated_at?->toJSON();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $fresh = $record->fresh();

        if ($this->loadedUpdatedAt !== null
            && $fresh?->updated_at?->toJSON() !== $this->loadedUpdatedAt) {
            Notification::make()
                ->danger()
                ->title('Data sudah diubah dari perangkat lain, silakan muat ulang')
                ->send();

            $this->halt();
        }

        $record->update($data);

        $this->loadedUpdatedAt = $record->fresh()->updated_at?->toJSON();

        return $record;
    }
}

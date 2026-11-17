<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'building_id', 'number', 'type', 'capacity',
        'price_monthly', 'price_quarterly', 'price_yearly',
        'special_status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price_monthly' => 'integer',
            'price_quarterly' => 'integer',
            'price_yearly' => 'integer',
        ];
    }

    /** Nominal DP: persentase (config babekost.dp_percent) dari harga bulanan. */
    public function getDpAmountAttribute(): int
    {
        return (int) round($this->price_monthly * config('babekost.dp_percent', 50) / 100);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /** Foto milik kamar ini. */
    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class)->orderBy('id');
    }

    /** Foto gedung kamar ini (yang tidak terikat ke kamar tertentu), dipakai sebagai cadangan. */
    public function buildingPhotos(): HasMany
    {
        return $this->hasMany(Gallery::class, 'building_id', 'building_id')
            ->whereNull('room_id')
            ->orderBy('id');
    }

    public function getPhotoListAttribute(): Collection
    {
        // 1) foto khusus kamar ini
        $own = $this->galleries->whereNotNull('photo_path')->values();
        if ($own->isNotEmpty()) {
            return $own;
        }

        $building = $this->buildingPhotos->whereNotNull('photo_path');

        // 2) foto kamar yang dipakai bersama oleh semua kamar di bangunan ini
        $shared = $building->where('category', 'kamar')->values();
        if ($shared->isNotEmpty()) {
            return $shared;
        }

        // 3) foto gedung sebagai cadangan terakhir
        return $building->values();
    }

    /** Foto sampul untuk kartu kamar; null kalau belum ada foto sama sekali. */
    public function getCoverUrlAttribute(): ?string
    {
        return $this->photo_list->first()?->url;
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_room')
            ->withPivot(['quantity', 'condition', 'notes']);
    }

    /**
     * Kamar yang bisa disewa pada rentang tanggal tertentu: kondisinya
     * normal dan tidak bertabrakan dengan booking yang masih berjalan.
     * Dipakai untuk menampilkan katalog; pengecekan final saat membuat
     * booking tetap harus di dalam transaction dengan lockForUpdate().
     */
    public function scopeAvailableBetween(Builder $query, $start, $end): Builder
    {
        return $query->where('special_status', 'normal')
            ->whereDoesntHave('bookings', function (Builder $q) use ($start, $end) {
                $q->whereIn('status', Booking::ACTIVE_STATUSES)
                    ->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            });
    }
}
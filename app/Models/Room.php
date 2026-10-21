<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_id',
        'number',
        'type',
        'facilities',
        'price_monthly',
        'price_quarterly',
        'price_yearly',
        'special_status',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RoomPhoto::class)->orderBy('display_order');
    }

    /**
     * Harga untuk paket tertentu. Dipakai nanti (Hari 8) saat menghitung
     * total_price otomatis pada pembuatan rental.
     */
    public function priceForPackage(string $package): int
    {
        return match ($package) {
            'monthly' => $this->price_monthly,
            'quarterly' => $this->price_quarterly,
            'yearly' => $this->price_yearly,
        };
    }
}

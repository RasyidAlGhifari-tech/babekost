<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseRule extends Model
{
    protected $fillable = [
        'content',
    ];

    /**
     * Selalu ambil (atau buat) satu-satunya baris tata tertib. Dipakai
     * oleh halaman admin (ManageHouseRules) dan nanti oleh alur booking
     * (Hari 10) untuk menampilkan teksnya ke penyewa.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'content' => 'Tata tertib belum diisi oleh pemilik.',
        ]);
    }
}

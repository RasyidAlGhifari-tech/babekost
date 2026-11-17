<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use SoftDeletes;

    protected $fillable = ['category', 'building_id', 'room_id', 'title', 'photo_path', 'description'];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    // URL publik foto, mengikuti alamat yang sedang dibuka (tidak bergantung APP_URL).
    protected function url(): Attribute
    {
        return Attribute::get(fn () => $this->photo_path ? asset('storage/' . ltrim($this->photo_path, '/')) : null);
    }
}
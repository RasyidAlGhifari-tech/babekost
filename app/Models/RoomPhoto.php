<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomPhoto extends Model
{
    protected $fillable = [
        'room_id',
        'path',
        'display_order',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}

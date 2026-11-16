<?php

namespace App\Support;

use App\Models\Room;
use Carbon\Carbon;

class Packages
{
    public const MONTHS = ['monthly' => 1, 'quarterly' => 3, 'yearly' => 12];

    public const LABELS = ['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', 'yearly' => 'Tahunan'];

    /** Tanggal terakhir masa sewa (inklusif). */
    public static function end(Carbon $start, string $package): Carbon
    {
        return $start->copy()->addMonthsNoOverflow(self::MONTHS[$package])->subDay();
    }

    public static function price(Room $room, string $package): int
    {
        return (int) match ($package) {
            'monthly'   => $room->price_monthly,
            'quarterly' => $room->price_quarterly,
            'yearly'    => $room->price_yearly,
        };
    }
}
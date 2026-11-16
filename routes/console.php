<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Status booking otomatis: selesai, batal jika tidak dibayar, pengingat perpanjangan.
Schedule::command('bookings:sync')->dailyAt('00:10')->withoutOverlapping();
<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Building;
use App\Models\Facility;
use App\Models\Gallery;
use App\Models\Promo;
use App\Models\Room;

class HomeController extends Controller
{
    // Nama kategori galeri yang dipakai sebagai banner Home.
    private const BANNER_CATEGORY = 'bangunan';

    public function __invoke()
    {
        $today = today();

        $building = Building::orderBy('id')->first();

        // Semua kamar yang kondisinya normal (bukan perbaikan/nonaktif).
        $rentable   = Room::where('special_status', 'normal');
        $totalRooms = (clone $rentable)->count();
        $startPrice = (clone $rentable)->min('price_yearly');

        // Kamar kosong hari ini, memakai scope di model Room.
        $available      = Room::availableBetween($today, $today);
        $availableCount = (clone $available)->count();
        $quickRooms     = (clone $available)->orderBy('number')->limit(2)->get();

        $facilities = Facility::orderBy('name')->limit(8)->pluck('name');

        // Foto banner carousel (maksimal 5).
        $banners = Gallery::where('category', self::BANNER_CATEGORY)
            ->whereNotNull('photo_path')
            ->orderBy('id')->limit(5)->get();

        $promos = Promo::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today))
            ->latest()->limit(1)->get();

        $agendas = Agenda::where('status', 'rencana')
            ->whereDate('agenda_date', '>=', $today)
            ->orderBy('agenda_date')->limit(1)->get();

        return view('visitor.home', compact(
            'building', 'banners', 'totalRooms', 'availableCount', 'quickRooms',
            'startPrice', 'facilities', 'promos', 'agendas'
        ));
    }
}
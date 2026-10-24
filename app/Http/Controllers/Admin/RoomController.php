<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $today  = today();
        $filter = in_array($request->query('status'), ['kosong', 'terisi', 'terbooking']) ? $request->query('status') : 'semua';
        $search = trim((string) $request->query('q', ''));
        $bid    = (int) $request->query('building');

        $kosongIds = Room::availableBetween($today, $today)->pluck('id')->flip();
        $terisiIds = Room::whereHas('bookings', fn ($q) => $q->where('status', 'aktif')
            ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today))->pluck('id')->flip();
        $bookIds   = Room::whereHas('bookings', fn ($q) => $q->whereIn('status', ['menunggu_pembayaran', 'booking'])
            ->whereDate('end_date', '>=', $today))->pluck('id')->flip();

        $rooms = Room::with(['building', 'bookings' => fn ($q) => $q->where('status', 'aktif')
                ->whereDate('end_date', '>=', $today)->orderBy('end_date')])
            ->when($bid, fn ($q) => $q->where('building_id', $bid))
            ->when($search !== '', fn ($q) => $q->where('number', 'like', "%{$search}%"))
            ->orderBy('building_id')->orderBy('id')->get()
            ->filter(fn ($r) => match ($filter) {
                'kosong'     => isset($kosongIds[$r->id]),
                'terisi'     => isset($terisiIds[$r->id]),
                'terbooking' => isset($bookIds[$r->id]),
                default      => true,
            });

        return view('admin.rooms', [
            'rooms'     => $rooms,
            'buildings' => Building::orderBy('name')->get(),
            'kosongIds' => $kosongIds, 'terisiIds' => $terisiIds, 'bookIds' => $bookIds,
            'counts'    => ['semua' => Room::count(), 'kosong' => $kosongIds->count(),
                            'terisi' => $terisiIds->count(), 'terbooking' => $bookIds->count()],
            'filter' => $filter, 'search' => $search, 'bid' => $bid,
        ]);
    }
}
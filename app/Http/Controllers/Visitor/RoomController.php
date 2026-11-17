<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $today  = today();
        $filter = $request->query('status', 'semua'); // semua | kosong | terisi
        $search = trim((string) $request->query('q', ''));

        $availableIds = Room::availableBetween($today, $today)->pluck('id');

        $total  = Room::count();
        $counts = [
            'semua'  => $total,
            'kosong' => $availableIds->count(),
            'terisi' => $total - $availableIds->count(),
        ];

        $rooms = Room::with(['building', 'galleries', 'buildingPhotos'])
            ->when($search !== '', fn ($q) => $q->where('number', 'like', "%{$search}%"))
            ->when($filter === 'kosong', fn ($q) => $q->whereIn('id', $availableIds))
            ->when($filter === 'terisi', fn ($q) => $q->whereNotIn('id', $availableIds))
            ->orderBy('number')
            ->get();

        return view('visitor.rooms.index', [
            'rooms'        => $rooms,
            'availableIds' => $availableIds->flip(), // untuk cek cepat isset()
            'counts'       => $counts,
            'filter'       => $filter,
            'search'       => $search,
        ]);
    }

    public function show(Room $room)
    {
        $today = today();

        $room->load(['building', 'facilities', 'galleries', 'buildingPhotos']);

        $isAvailable = Room::availableBetween($today, $today)
            ->whereKey($room->id)
            ->exists();

        return view('visitor.rooms.show', compact('room', 'isAvailable'));
    }
}
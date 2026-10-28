<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomRequest;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Facility;
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

    public function create()
    {
        return view('admin.rooms-form', [
            'room'       => new Room(['capacity' => 1, 'special_status' => 'normal']),
            'buildings'  => Building::orderBy('name')->get(),
            'facilities' => Facility::orderBy('name')->get(),
            'selected'   => [],
        ]);
    }

    public function store(RoomRequest $request)
    {
        $data = $request->validated();
        $room = Room::create(collect($data)->except('facilities')->all());
        $room->facilities()->sync($data['facilities'] ?? []);

        return redirect()->route('admin.rooms')->with('status', "Kamar {$room->number} ditambahkan.");
    }

    public function edit(Room $room)
    {
        return view('admin.rooms-form', [
            'room'       => $room,
            'buildings'  => Building::orderBy('name')->get(),
            'facilities' => Facility::orderBy('name')->get(),
            'selected'   => $room->facilities()->pluck('facilities.id')->all(),
        ]);
    }

    public function update(RoomRequest $request, Room $room)
    {
        $data = $request->validated();

        if ($data['special_status'] !== 'normal' && $this->hasOccupant($room)) {
            return back()->withInput()->withErrors([
                'special_status' => 'Kamar sedang dihuni, tidak bisa diubah ke perbaikan/nonaktif.',
            ]);
        }

        $room->update(collect($data)->except('facilities')->all());
        $room->facilities()->sync($data['facilities'] ?? []);

        return redirect()->route('admin.rooms')->with('status', "Kamar {$room->number} diperbarui.");
    }

    public function status(Request $request, Room $room)
    {
        $status = $request->validate([
            'special_status' => ['required', 'in:normal,perbaikan,nonaktif'],
        ])['special_status'];

        if ($status !== 'normal' && $this->hasOccupant($room)) {
            return back()->withErrors(['room' => "Kamar {$room->number} sedang dihuni."]);
        }

        $room->update(['special_status' => $status]);

        return back()->with('status', "Status kamar {$room->number} diubah.");
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('status', Booking::ACTIVE_STATUSES)->exists()) {
            return back()->withErrors(['room' => "Kamar {$room->number} masih punya booking aktif, tidak bisa dihapus."]);
        }

        $room->delete();

        return back()->with('status', "Kamar {$room->number} dihapus.");
    }

    private function hasOccupant(Room $room): bool
    {
        return $room->bookings()->where('status', 'aktif')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())->exists();
    }
}
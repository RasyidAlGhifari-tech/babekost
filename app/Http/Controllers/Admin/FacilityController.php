<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Room;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'kamar' ? 'kamar' : 'master';

        $facilities = Facility::orderBy('name')->get();
        $rooms      = Room::with('building')->orderBy('building_id')->orderBy('id')->get();
        $room       = $rooms->firstWhere('id', (int) $request->query('room')) ?? $rooms->first();
        $room?->load('facilities');

        return view('admin.facilities', compact('tab', 'facilities', 'rooms', 'room'));
    }

    public function store(Request $request)
    {
        Facility::create($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Fasilitas ditambahkan.');
    }

    public function update(Request $request, Facility $facility)
    {
        $facility->update($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Fasilitas diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return back()->with('status', 'Fasilitas dihapus.');
    }

    public function attach(Request $request, Room $room)
    {
        $data = $request->validate([
            'facility_id' => ['required', 'exists:facilities,id'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:99'],
            'condition'   => ['nullable', 'string', 'max:50'],
        ]);

        $room->facilities()->syncWithoutDetaching([
            $data['facility_id'] => ['quantity' => $data['quantity'], 'condition' => $data['condition'] ?? null],
        ]);

        return back()->with('status', 'Fasilitas dipasang ke kamar.');
    }

    public function detach(Room $room, Facility $facility)
    {
        $room->facilities()->detach($facility->id);

        return back()->with('status', 'Fasilitas dilepas dari kamar.');
    }
}
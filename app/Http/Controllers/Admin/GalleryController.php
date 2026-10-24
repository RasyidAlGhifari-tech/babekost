<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Gallery;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'galeri' ? 'galeri' : 'bangunan';

        return view('admin.gallery', [
            'tab'       => $tab,
            'buildings' => Building::orderBy('name')->get(),
            'rooms'     => Room::orderBy('building_id')->orderBy('id')->get(),
            'photos'    => Gallery::with('room')->latest()->get(),
        ]);
    }

    public function storeBuilding(Request $request)
    {
        Building::create($request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'segment' => ['required', 'in:putra,putri'],
        ]));

        return back()->with('status', 'Bangunan ditambahkan.');
    }

    public function destroyBuilding(Building $building)
    {
        if ($building->rooms()->exists()) {
            return back()->withErrors(['building' => 'Bangunan masih memiliki kamar, tidak bisa dihapus.']);
        }
        $building->delete();

        return back()->with('status', 'Bangunan dihapus.');
    }

    public function storePhoto(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'in:bangunan,kamar,fasilitas'],
            'room_id'  => ['nullable', 'exists:rooms,id'],
            'title'    => ['nullable', 'string', 'max:100'],
            'photo'    => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        Gallery::create([
            'category'   => $data['category'],
            'room_id'    => $data['category'] === 'kamar' ? ($data['room_id'] ?? null) : null,
            'title'      => $data['title'] ?? null,
            'photo_path' => $request->file('photo')->store('galleries', 'public'),
        ]);

        return redirect()->route('admin.gallery', ['tab' => 'galeri'])->with('status', 'Foto diunggah.');
    }

    public function destroyPhoto(Gallery $gallery)
    {
        Storage::disk('public')->delete($gallery->photo_path);
        $gallery->delete();

        return back()->with('status', 'Foto dihapus.');
    }
}
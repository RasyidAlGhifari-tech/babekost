<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    private function currentRoom($tenant)
    {
        return $tenant->bookings()
            ->whereIn('status', ['booking', 'aktif'])
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->with('room')->latest('end_date')->first()?->room;
    }

    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $filter = in_array($request->query('status'), ['baru', 'diproses', 'selesai']) ? $request->query('status') : null;

        $complaints = $tenant->complaints()->with('room')
            ->when($filter, fn ($q) => $q->where('status', $filter))
            ->latest()->get();

        return view('tenant.complaints', [
            'room'       => $this->currentRoom($tenant),
            'complaints' => $complaints,
            'filter'     => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $data = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $tenant->complaints()->create([
            'room_id'     => $this->currentRoom($tenant)?->id,
            'description' => $data['description'],
        ]);

        return redirect()->route('tenant.complaints', ['tab' => 'riwayat'])
            ->with('status', 'Keluhan terkirim.');
    }
}
<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;

        $rooms = Room::with('building')
            ->where('special_status', 'normal')
            ->orderBy('building_id')->orderBy('id')->get()
            ->map(fn ($r) => [
                'id'       => $r->id,
                'label'    => $r->building->name . ' • ' . $r->number,
                'capacity' => $r->capacity,
                'monthly'  => $r->price_monthly,
                'yearly'   => $r->price_yearly,
            ])->values();

        $bookings = $tenant->bookings()->with('room.building')->latest('start_date')->get();

        return view('tenant.booking', compact('rooms', 'bookings'));
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $data = $request->validate([
            'room_id'    => ['required', 'exists:rooms,id'],
            'package'    => ['required', 'in:monthly,yearly'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = ($data['package'] === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth())->subDay();

        DB::transaction(function () use ($data, $tenant, $start, $end) {
            $room = Room::whereKey($data['room_id'])->lockForUpdate()->firstOrFail();

            $free = Room::availableBetween($start, $end)->whereKey($room->id)->exists();
            if (! $free) {
                throw ValidationException::withMessages([
                    'room_id' => 'Kamar tidak tersedia pada tanggal tersebut.',
                ]);
            }

            Booking::create([
                'tenant_id'      => $tenant->id,
                'room_id'        => $room->id,
                'package'        => $data['package'],
                'start_date'     => $start,
                'end_date'       => $end,
                'total_price'    => $data['package'] === 'yearly' ? $room->price_yearly : $room->price_monthly,
                'deposit_amount' => 0, // ASUMSI: belum ada aturan DP. Ubah bila ada.
                'status'         => 'menunggu_pembayaran',
            ]);
        });

        return redirect()->route('tenant.booking', ['tab' => 'saya'])
            ->with('status', 'Booking diajukan. Silakan unggah bukti pembayaran.');
    }
}
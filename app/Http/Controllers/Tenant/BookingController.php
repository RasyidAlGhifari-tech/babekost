<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Support\Notifier;
use App\Support\Packages;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;

        // Mode perpanjangan: hanya booking milik sendiri yang berstatus aktif.
        $extend = $request->filled('extend')
            ? $tenant->bookings()->with('room.building')->where('status', 'aktif')->find($request->query('extend'))
            : null;

        $rooms = Room::with('building')
            ->where('special_status', 'normal')
            ->when($extend, fn ($q) => $q->whereKey($extend->room_id))
            ->orderBy('building_id')->orderBy('id')->get()
            ->map(fn ($r) => [
                'id'        => $r->id,
                'label'     => $r->building->name . ' • ' . $r->number,
                'capacity'  => $r->capacity,
                'monthly'   => $r->price_monthly,
                'quarterly' => $r->price_quarterly,
                'yearly'    => $r->price_yearly,
                'dp'        => $r->dp_amount,
            ])->values();

        // Kamar yang dipilih dari halaman detail (?room=ID), bila valid.
        $preselect = $rooms->contains('id', $request->integer('room')) ? $request->integer('room') : null;

        $bookings = $tenant->bookings()->with('room.building')->latest('start_date')->get();

        return view('tenant.booking', compact('rooms', 'bookings', 'extend', 'preselect'));
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $data = $request->validate([
            'room_id'           => ['required', 'exists:rooms,id'],
            'package'           => ['required', 'in:monthly,quarterly,yearly'],
            'start_date'        => ['required', 'date', 'after_or_equal:today'],
            'agree_rules'       => ['accepted'],
            'extend_booking_id' => ['nullable', 'integer'],
        ], [
            'agree_rules.accepted' => 'Anda harus menyetujui tata tertib kost.',
        ]);

        // Perpanjangan: kamar dan tanggal mulai ditentukan server, bukan form.
        $extendFrom = null;
        if (! empty($data['extend_booking_id'])) {
            $extendFrom = $tenant->bookings()->where('status', 'aktif')->find($data['extend_booking_id']);

            if (! $extendFrom) {
                throw ValidationException::withMessages([
                    'room_id' => 'Booking yang ingin diperpanjang tidak ditemukan atau belum aktif.',
                ]);
            }

            $data['room_id']    = $extendFrom->room_id;
            $data['start_date'] = $extendFrom->end_date->copy()->addDay()->toDateString();

            $already = $tenant->bookings()
                ->where('room_id', $extendFrom->room_id)
                ->whereDate('start_date', $data['start_date'])
                ->whereIn('status', Booking::ACTIVE_STATUSES)
                ->exists();

            if ($already) {
                throw ValidationException::withMessages([
                    'room_id' => 'Perpanjangan untuk periode ini sudah diajukan.',
                ]);
            }
        }

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Packages::end($start, $data['package']);

        $booking = DB::transaction(function () use ($data, $tenant, $start, $end, $extendFrom) {
            $room = Room::whereKey($data['room_id'])->lockForUpdate()->firstOrFail();

            $free = Room::availableBetween($start, $end)->whereKey($room->id)->exists();
            if (! $free) {
                throw ValidationException::withMessages([
                    'room_id' => 'Kamar tidak tersedia pada tanggal tersebut.',
                ]);
            }

            $total = Packages::price($room, $data['package']);

            $booking = new Booking();
            $booking->forceFill([
                'tenant_id'             => $tenant->id,
                'room_id'               => $room->id,
                'package'               => $data['package'],
                'start_date'            => $start,
                'end_date'              => $end,
                'total_price'           => $total,
                'deposit_amount'        => min($room->dp_amount, $total),
                'status'                => 'menunggu_pembayaran',
                'house_rules_agreed_at' => now(),
                'notes'                 => $extendFrom ? "Perpanjangan dari booking #{$extendFrom->id}." : null,
            ])->save();

            return $booking->load('room');
        });

        Notifier::toAdmins(
            $extendFrom ? 'Perpanjangan sewa' : 'Booking baru',
            $extendFrom
                ? "Penyewa mengajukan perpanjangan Kamar {$booking->room->number}."
                : "Booking baru untuk Kamar {$booking->room->number}.",
            route('admin.bookings', [], false),
            'calendar-days'
        );

        return redirect()->route('tenant.booking', ['tab' => 'saya'])
            ->with('status', $extendFrom
                ? 'Perpanjangan diajukan. Silakan bayar DP.'
                : 'Booking diajukan. Silakan bayar DP.');
    }
}
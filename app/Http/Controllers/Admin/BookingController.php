<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private const GROUPS = [
        'pending' => ['menunggu_pembayaran'],
        'aktif'   => ['booking', 'aktif'],
        'selesai' => ['selesai', 'dibatalkan'],
    ];

    public function index(Request $request)
    {
        $tab = array_key_exists($request->query('tab'), self::GROUPS) ? $request->query('tab') : 'pending';

        $counts = collect(self::GROUPS)->map(fn ($s) => Booking::whereIn('status', $s)->count());

        $bookings = Booking::with('room', 'tenant.user')
            ->whereIn('status', self::GROUPS[$tab])->latest('start_date')->get();

        return view('admin.bookings', compact('tab', 'counts', 'bookings'));
    }

    // ASUMSI: "Setuju" = booking diterima (status booking). Lunas ditetapkan saat verifikasi pembayaran.
    public function approve(Booking $booking)
    {
        abort_unless($booking->status === 'menunggu_pembayaran', 422);
        $booking->update(['status' => 'booking']);

        return back()->with('status', 'Booking disetujui.');
    }

    public function reject(Booking $booking)
    {
        abort_unless($booking->status === 'menunggu_pembayaran', 422);
        $booking->update([
            'status' => 'dibatalkan',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Ditolak pemilik.',
        ]);

        return back()->with('status', 'Booking ditolak.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\Room;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = today();

        $total      = Room::where('special_status', 'normal')->count();
        $kosong     = Room::availableBetween($today, $today)->count();
        $terisi     = Room::whereHas('bookings', fn ($q) => $q->where('status', 'aktif')
                        ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today))->count();
        $terbooking = Room::whereHas('bookings', fn ($q) => $q->whereIn('status', ['menunggu_pembayaran', 'booking'])
                        ->whereDate('end_date', '>=', $today))->count();
        $occupancy  = $total ? (int) round($terisi / $total * 100) : 0;

        // Pemasukan 6 bulan terakhir (pembayaran terverifikasi).
        $months = collect(range(5, 0))->map(function ($i) {
            $m = today()->startOfMonth()->subMonths($i);
            return [
                'label' => $m->translatedFormat('M'),
                'sum'   => (int) Payment::where('status', 'terverifikasi')
                    ->whereYear('paid_at', $m->year)->whereMonth('paid_at', $m->month)->sum('amount'),
            ];
        });
        $max = max($months->max('sum'), 1);

        $todo = [
            'pending'    => Payment::where('status', 'menunggu_verifikasi')->count(),
            'ending'     => Booking::where('status', 'aktif')
                                ->whereBetween('end_date', [$today, $today->copy()->addDays(7)])->count(),
            'tomorrow'   => Booking::whereIn('status', ['booking', 'menunggu_pembayaran'])
                                ->whereDate('start_date', $today->copy()->addDay())->count(),
            'complaints' => Complaint::where('status', 'baru')->count(),
        ];

        $recent = Payment::with('booking.room')->latest()->limit(3)->get();

        return view('admin.dashboard', compact(
            'total', 'kosong', 'terisi', 'terbooking', 'occupancy', 'months', 'max', 'todo', 'recent'
        ));
    }
}
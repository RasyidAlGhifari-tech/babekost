<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Payment;
use App\Models\Promo;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __invoke()
    {
        $today  = today();
        $tenant = auth()->user()->tenant;

        // Kamar aktif: booking sudah terverifikasi (booking/aktif) dan masa sewa berjalan.
        $booking = $tenant?->bookings()
            ->whereIn('status', ['booking', 'aktif'])
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('room.building')
            ->latest('end_date')
            ->first();

        $daysLeft = $progress = null;
        if ($booking) {
            $start = Carbon::parse($booking->start_date)->startOfDay();
            $end   = Carbon::parse($booking->end_date)->startOfDay();
            $total = max($start->diffInDays($end), 1);
            $daysLeft = (int) $today->diffInDays($end, false);
            $progress = (int) round(max(0, min(100, ($total - $daysLeft) / $total * 100)));
        }

        $lastPayment = $tenant
            ? Payment::whereHas('booking', fn ($q) => $q->where('tenant_id', $tenant->id))->latest()->first()
            : null;

        $lastComplaint = $tenant?->complaints()->latest()->first();

        $promo  = Promo::where('is_active', true)->latest()->first();
        $agenda = Agenda::where('status', 'rencana')
            ->whereDate('agenda_date', '>=', $today)
            ->orderBy('agenda_date')->first();

        return view('tenant.home', compact(
            'tenant', 'booking', 'daysLeft', 'progress',
            'lastPayment', 'lastComplaint', 'promo', 'agenda'
        ));
    }
}
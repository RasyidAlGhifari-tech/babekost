<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Support\Packages;
use App\Support\Terbilang;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function __invoke(Payment $payment)
    {
        $user = auth()->user();
        $payment->load('booking.room.building', 'booking.tenant.user');
        $booking = $payment->booking;

        // Admin boleh semua; penyewa hanya kwitansi miliknya.
        abort_unless($user->isAdmin() || $booking->tenant->user_id === $user->id, 403);

        // Kwitansi hanya ada setelah pembayaran diverifikasi.
        abort_unless($payment->status === 'terverifikasi' && $payment->receipt_number, 404);

        $paidToDate = (int) $booking->payments()
            ->where('status', 'terverifikasi')
            ->where('id', '<=', $payment->id)
            ->sum('amount');

        $logo = null;
        $logoPath = public_path('images/logo.png');
        if (is_file($logoPath) && extension_loaded('gd')) {
            $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment'    => $payment,
            'booking'    => $booking,
            'room'       => $booking->room,
            'building'   => $booking->room->building,
            'payer'      => $booking->tenant->user,
            'package'    => Packages::LABELS[$booking->package] ?? $booking->package,
            'paidToDate' => $paidToDate,
            'remaining'  => max(0, $booking->total_price - $paidToDate),
            'words'      => Terbilang::rupiah((int) $payment->amount),
            'bank'       => config('babekost.bank'),
            'logo'       => $logo,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream('Kwitansi-' . $payment->receipt_number . '.pdf');
    }
}
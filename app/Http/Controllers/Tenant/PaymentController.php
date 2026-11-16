<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    private function rp(int $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    /** Batas jumlah yang boleh dibayar untuk satu booking. */
    private function limits($booking): array
    {
        $paid      = (int) $booking->payments()->where('status', 'terverifikasi')->sum('amount');
        $remaining = max(0, $booking->total_price - $paid);

        if ($booking->status === 'menunggu_pembayaran') {
            $min  = max(1, min($booking->deposit_amount, $remaining));
            $hint = $min < $remaining
                ? 'DP minimal ' . $this->rp($min) . ', sisa ' . $this->rp($remaining - $min) . ' dilunasi kemudian. Boleh juga langsung lunas ' . $this->rp($remaining) . '.'
                : 'Bayar ' . $this->rp($remaining) . '.';
        } else {
            $min  = $remaining;
            $hint = 'Pelunasan ' . $this->rp($remaining) . '.';
        }

        return ['min' => $min, 'max' => $remaining, 'hint' => $hint];
    }

    public function index()
    {
        $tenant = auth()->user()->tenant;

        $bookings = $tenant->bookings()
            ->whereIn('status', ['menunggu_pembayaran', 'booking', 'aktif'])
            ->with('room')->latest('start_date')->get();

        $info = [];
        foreach ($bookings as $b) {
            $info[$b->id] = $this->limits($b);
        }

        // Hanya booking yang masih ada sisa tagihan.
        $bookings = $bookings->filter(fn ($b) => $info[$b->id]['max'] > 0)->values();
        $info     = collect($info)->only($bookings->pluck('id')->all())->all();

        $payments = Payment::whereHas('booking', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->with('booking.room')->latest()->get();

        return view('tenant.payment', compact('bookings', 'payments', 'info'));
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount'     => ['required', 'integer', 'min:1'],
            'proof'      => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        // Hanya booking milik penyewa ini.
        $booking = $tenant->bookings()->findOrFail($data['booking_id']);

        if (! in_array($booking->status, ['menunggu_pembayaran', 'booking'])) {
            throw ValidationException::withMessages(['amount' => 'Booking ini tidak memerlukan pembayaran.']);
        }

        if ($booking->payments()->where('status', 'menunggu_verifikasi')->exists()) {
            throw ValidationException::withMessages(['amount' => 'Masih ada pembayaran yang menunggu verifikasi admin.']);
        }

        $limit = $this->limits($booking);

        if ($limit['max'] <= 0) {
            throw ValidationException::withMessages(['amount' => 'Booking ini sudah lunas.']);
        }
        if ($data['amount'] < $limit['min'] || $data['amount'] > $limit['max']) {
            throw ValidationException::withMessages(['amount' => $limit['hint']]);
        }

        $payment = Payment::create([
            'booking_id'          => $booking->id,
            'type'                => $data['amount'] >= $limit['max'] ? 'pelunasan' : 'dp',
            'amount'              => $data['amount'],
            'paid_at'             => today(),
            'destination_account' => 'suami', // satu rekening BCA; kolom enum dipertahankan.
            'proof_path'          => $request->file('proof')->store('payment-proofs'), // disk privat
        ]);

        Notifier::toAdmins(
            'Bukti pembayaran baru',
            "Kamar {$booking->room->number} mengirim bukti pembayaran " . $this->rp((int) $payment->amount) . '.',
            route('admin.finance', [], false),
            'banknotes'
        );

        return redirect()->route('tenant.payment', ['tab' => 'riwayat'])
            ->with('status', 'Bukti pembayaran terkirim, menunggu verifikasi.');
    }
}
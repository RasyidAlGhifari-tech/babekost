<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;

        $bookings = $tenant->bookings()
            ->whereIn('status', ['menunggu_pembayaran', 'booking', 'aktif'])
            ->with('room')->latest('start_date')->get();

        $payments = Payment::whereHas('booking', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->with('booking.room')->latest()->get();

        return view('tenant.payment', compact('bookings', 'payments'));
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

        Payment::create([
            'booking_id'          => $booking->id,
            'type'                => $booking->status === 'menunggu_pembayaran' ? 'dp' : 'pelunasan',
            'amount'              => $data['amount'],
            'paid_at'             => today(),
            'destination_account' => 'suami', // ASUMSI: Figma tidak memiliki pilihan rekening.
            'proof_path'          => $request->file('proof')->store('payment-proofs'), // disk privat
        ]);

        return redirect()->route('tenant.payment', ['tab' => 'riwayat'])
            ->with('status', 'Bukti pembayaran terkirim, menunggu verifikasi.');
    }
}
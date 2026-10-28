<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TenantRegistrationController extends Controller
{
    public function create()
    {
        $rooms = Room::with('building')
            ->where('special_status', 'normal')
            ->orderBy('building_id')->orderBy('id')->get();

        return view('admin.accounts-create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255',
                                    Rule::unique('users', 'email')->whereNull('deleted_at')],
            'phone'             => ['nullable', 'string', 'max:20'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'campus_or_job'     => ['nullable', 'string', 'max:255'],

            'room_id'           => ['required', 'exists:rooms,id'],
            'package'           => ['required', 'in:monthly,yearly'],
            'start_date'        => ['required', 'date'],
            'custom_price'      => ['nullable', 'integer', 'min:0'],
            'status'            => ['required', 'in:aktif,booking,menunggu_pembayaran'],

            'mark_paid'          => ['nullable', 'boolean'],
            'destination_account' => ['required_if:mark_paid,1', 'nullable', 'in:suami,istri'],
            'paid_at'            => ['required_if:mark_paid,1', 'nullable', 'date'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        $markPaid = (bool) ($data['mark_paid'] ?? false);

        if ($markPaid && $data['status'] === 'menunggu_pembayaran') {
            throw ValidationException::withMessages([
                'status' => 'Booking yang sudah dibayar tidak boleh berstatus menunggu pembayaran.',
            ]);
        }

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = ($data['package'] === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth())->subDay();

        $password = Str::password(10, symbols: false);

        $user = DB::transaction(function () use ($data, $markPaid, $start, $end, $password) {
            $room = Room::whereKey($data['room_id'])->lockForUpdate()->firstOrFail();

            $free = $room->special_status === 'normal'
                && Room::availableBetween($start, $end)->whereKey($room->id)->exists();

            if (! $free) {
                throw ValidationException::withMessages([
                    'room_id' => 'Kamar tidak tersedia pada rentang tanggal tersebut.',
                ]);
            }

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => $password,
            ]);

            $tenant = $user->tenant()->create([
                'phone'             => $user->phone,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'campus_or_job'     => $data['campus_or_job'] ?? null,
            ]);

            $price = $data['custom_price'] ?? ($data['package'] === 'yearly' ? $room->price_yearly : $room->price_monthly);

            $booking = Booking::create([
                'tenant_id'      => $tenant->id,
                'room_id'        => $room->id,
                'package'        => $data['package'],
                'start_date'     => $start,
                'end_date'       => $end,
                'total_price'    => $price,
                'deposit_amount' => 0, // ASUMSI: belum ada aturan DP.
                'status'         => $data['status'],
                'notes'          => 'Dicatat manual oleh admin.',
            ]);

            if ($markPaid) {
                $payment = new Payment();
                $payment->forceFill([
                    'booking_id'          => $booking->id,
                    'verified_by'         => auth()->user()->admin?->id,
                    'type'                => 'pelunasan',
                    'amount'              => $price,
                    'paid_at'             => $data['paid_at'],
                    'destination_account' => $data['destination_account'],
                    'proof_path'          => '', // dicatat manual, tanpa bukti unggahan
                    'status'              => 'terverifikasi',
                    'verified_at'         => now(),
                    'notes'               => 'Dicatat manual oleh admin.',
                ])->save();

                $payment->update([
                    'receipt_number' => 'KW-' . now()->format('Ymd') . '-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT),
                ]);
            }

            return $user;
        });

        return redirect()->route('admin.accounts.create')
            ->with('status', "Penyewa {$user->name} berhasil ditambahkan.")
            ->with('credentials', ['email' => $user->email, 'password' => $password]);
    }
}
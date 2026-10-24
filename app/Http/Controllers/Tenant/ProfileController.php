<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function show()
    {
        $booking = auth()->user()->tenant?->bookings()
            ->whereIn('status', ['booking', 'aktif'])
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->with('room.facilities')
            ->latest('end_date')
            ->first();

        return view('tenant.profile', compact('booking'));
    }

    public function edit()
    {
        return view('tenant.profile-edit', [
            'user'   => auth()->user(),
            'tenant' => auth()->user()->tenant,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255',
                                    Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at')],
            'phone'             => ['nullable', 'regex:/^\+?[\d\s\-]{8,16}$/'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'campus_or_job'     => ['nullable', 'string', 'max:255'],
        ], [
            'email.unique' => 'Email sudah dipakai akun lain.',
            'phone.regex'  => 'Format nomor HP tidak valid.',
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => Str::lower($data['email']),
            'phone' => $data['phone'] ?? null,
        ]);

        $user->tenant()->updateOrCreate([], [
            'phone'             => $user->phone,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'campus_or_job'     => $data['campus_or_job'] ?? null,
        ]);

        return redirect()->route('tenant.profile')->with('status', 'Data diri diperbarui.');
    }

    public function password()
    {
        return view('tenant.profile-password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
        ]);

        $request->user()->update(['password' => $data['password']]); // di-hash oleh cast

        return redirect()->route('tenant.profile')->with('status', 'Password berhasil diubah.');
    }

    public function contact()
    {
        $owner = User::where('role', 'admin')->first();

        // 08xxxx -> 628xxxx untuk tautan WhatsApp
        $wa = $owner?->phone ? '62' . substr($owner->phone, 1) : '6281222223215';

        return view('tenant.profile-contact', compact('owner', 'wa'));
    }
}
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Pengganti RegisteredUserController bawaan Breeze. Perbedaan utama:
     *  - role SELALU dipaksa 'penyewa'. Tidak ada input role dari form,
     *    jadi registrasi admin memang tidak mungkin lewat jalur publik
     *    ini (bagian 2: "Registrasi admin tidak tersedia untuk umum").
     *  - Langsung membuat baris `tenants` sekaligus (no HP, kontak
     *    darurat, kampus/pekerjaan) — bagian 7: TANPA upload KTP, hanya
     *    data yang benar-benar diperlukan.
     *  - User + Tenant dibuat dalam satu DB::transaction supaya tidak
     *    ada User tanpa profil Tenant kalau terjadi error di tengah.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:30'],
            'emergency_contact' => ['required', 'string', 'max:255'],
            'campus_or_job' => ['nullable', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'penyewa',
            ]);

            Tenant::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'emergency_contact' => $validated['emergency_contact'],
                'campus_or_job' => $validated['campus_or_job'] ?? null,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        // TODO Hari 14: ganti ke route('kos-saya') setelah halaman itu ada.
        // Untuk sekarang pakai route 'dashboard' bawaan Breeze.
        return redirect(route('dashboard', absolute: false));
    }
}

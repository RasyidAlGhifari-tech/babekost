<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class MoreController extends Controller
{
    public function index()
    {
        return view('admin.more', [
            'pendingBookings' => Booking::where('status', 'menunggu_pembayaran')->count(),
            'newComplaints'   => Complaint::where('status', 'baru')->count(),
        ]);
    }

    public function password()
    {
        return view('admin.password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Rules\Password::min(8)],
        ], ['current_password.current_password' => 'Password lama salah.']);

        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('admin.more')->with('status', 'Password berhasil diubah.');
    }
}
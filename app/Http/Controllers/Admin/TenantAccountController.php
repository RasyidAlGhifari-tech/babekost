<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantAccountController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::where('role', 'penyewa')
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
            ->with(['tenant.bookings' => fn ($b) => $b->whereIn('status', ['booking', 'aktif'])
                ->whereDate('end_date', '>=', today())->with('room')])
            ->orderBy('name')->get();

        return view('admin.accounts', compact('users', 'q'));
    }

    public function reset(User $user)
    {
        abort_unless($user->role === 'penyewa', 403);

        $new = Str::lower(Str::random(8));
        $user->update(['password' => $new]);

        return back()->with('reset', ['name' => $user->name, 'password' => $new]);
    }

    public function toggle(User $user)
    {
        abort_unless($user->role === 'penyewa', 403);

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('status', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }
}
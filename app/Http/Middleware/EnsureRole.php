<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Middleware peran, dipakai sebagai middleware('role:admin') atau
     * middleware('role:penyewa'). Satu middleware generik dipakai ulang
     * untuk kedua peran (bagian 2 & Hari 4), daripada membuat middleware
     * terpisah per peran.
     *
     * Didaftarkan sebagai alias 'role' di bootstrap/app.php (lihat
     * SETUP-fase2.md).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Kamu tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255',
                            Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => Str::lower($request->email),
            'password' => $request->password, // di-hash oleh cast 'hashed'
        ]);

        $user->tenant()->create([]);

        Auth::login($user);

        return redirect()->route('tenant.home');
    }
}
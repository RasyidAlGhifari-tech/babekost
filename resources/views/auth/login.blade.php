<x-layouts.visitor title="Login">
    <x-slot:action>
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-tint-gold text-gold-700">
            <x-heroicon-o-arrow-right-end-on-rectangle class="h-5 w-5" />
        </span>
    </x-slot:action>
    <div class="px-4 pt-6">
        <img src="{{ asset('images/logo.png') }}" alt="Babe Kos" class="mx-auto h-16">

        <div class="mt-6"><x-auth-tabs active="login" /></div>
        <h2 class="mt-5 text-center text-base font-semibold">Masuk ke akun</h2>

        @if ($errors->any())
            <div class="mt-4 rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-4 space-y-3">
            @csrf
            <x-field name="email" type="email" icon="envelope" placeholder="Email" autocomplete="email" required autofocus />
            <x-field name="password" type="password" icon="lock-closed" placeholder="Password" autocomplete="current-password" required />
            <x-button type="submit">Login</x-button>
        </form>

        <p class="mt-3 text-center text-xs text-slate">
            Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-gold-700">Register</a>
        </p>
    </div>
</x-layouts.visitor>
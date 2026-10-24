<x-layouts.tenant title="Ubah password">
    <div class="flex flex-col gap-4 p-4">
        <a href="{{ route('tenant.profile') }}" class="flex items-center gap-1 text-xs font-medium text-gold-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" /> Kembali
        </a>

        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('tenant.profile.password.update') }}" class="flex flex-col gap-3">
            @csrf
            @method('PUT')
            <x-field name="current_password" type="password" icon="lock-closed" placeholder="Password saat ini" autocomplete="current-password" required />
            <x-field name="password" type="password" icon="lock-closed" placeholder="Password baru" autocomplete="new-password" required />
            <x-field name="password_confirmation" type="password" icon="lock-closed" placeholder="Ulangi password baru" autocomplete="new-password" required />
            <x-button type="submit">Simpan password</x-button>
        </form>
    </div>
</x-layouts.tenant>
<x-layouts.admin title="Ubah password">
    <div class="flex flex-col gap-3 p-4">
        <x-back />

        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" class="flex flex-col gap-3">
            @csrf @method('PUT')
            <x-field name="current_password" type="password" icon="lock-closed" placeholder="Password lama" autocomplete="current-password" required />
            <x-field name="password" type="password" icon="lock-closed" placeholder="Password baru (min. 8 karakter)" autocomplete="new-password" required />
            <x-field name="password_confirmation" type="password" icon="lock-closed" placeholder="Ulangi password baru" autocomplete="new-password" required />
            <x-button type="submit">Simpan password</x-button>
            <p class="text-center text-[11px] text-slate">Setelah disimpan, Anda tetap masuk di perangkat ini.</p>
        </form>
    </div>
</x-layouts.admin>
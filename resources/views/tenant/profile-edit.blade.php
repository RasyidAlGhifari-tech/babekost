<x-layouts.tenant title="Ubah data diri">
    <div class="flex flex-col gap-4 p-4">
        <a href="{{ route('tenant.profile') }}" class="flex items-center gap-1 text-xs font-medium text-gold-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" /> Kembali
        </a>

        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('tenant.profile.update') }}" class="flex flex-col gap-3">
            @csrf
            @method('PATCH')

            @foreach ([
                ['name',              'Nama lengkap',        'text',  $user->name,                  'user'],
                ['email',             'Email',               'email', $user->email,                 'envelope'],
                ['phone',             'Nomor HP (opsional)', 'tel',   $user->phone,                 'phone'],
                ['emergency_contact', 'Kontak darurat',      'text',  $tenant?->emergency_contact,  'phone-arrow-up-right'],
                ['campus_or_job',     'Kampus / pekerjaan',  'text',  $tenant?->campus_or_job,      'academic-cap'],
            ] as [$name, $label, $type, $value, $icon])
                <div>
                    <p class="mb-1 text-[11px] text-slate">{{ $label }}</p>
                    <div class="flex items-center gap-2 rounded-lg border border-line-chip bg-white px-3 focus-within:border-gold-700">
                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-5 w-5 shrink-0 text-muted" />
                        <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
                            class="w-full border-0 bg-transparent py-3 text-sm focus:ring-0">
                    </div>
                </div>
            @endforeach

            <x-button type="submit">Simpan perubahan</x-button>
        </form>
    </div>
</x-layouts.tenant>
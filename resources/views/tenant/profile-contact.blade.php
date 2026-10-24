<x-layouts.tenant title="Kontak pemilik">
    <div class="flex flex-col gap-4 p-4">
        <a href="{{ route('tenant.profile') }}" class="flex items-center gap-1 text-xs font-medium text-gold-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" /> Kembali
        </a>

        <x-card class="flex items-center gap-3 p-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-tint-gold text-gold-700">
                <x-heroicon-o-user class="h-6 w-6" />
            </span>
            <div>
                <p class="text-sm font-bold">{{ $owner?->name ?? 'Pemilik Babekost' }}</p>
                <p class="text-[11px] text-slate">Pemilik kos</p>
            </div>
        </x-card>

        <x-card class="divide-y divide-line p-0">
            <div class="flex items-center gap-3 px-4 py-3 text-xs">
                <x-heroicon-o-phone class="h-5 w-5 text-gold-700" />
                {{ $owner?->phone ?? '-' }}
            </div>
            <div class="flex items-center gap-3 px-4 py-3 text-xs">
                <x-heroicon-o-envelope class="h-5 w-5 text-gold-700" />
                {{ $owner?->email ?? '-' }}
            </div>
        </x-card>

        <x-button href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo, saya ' . auth()->user()->name . ' penghuni Babekost.') }}">
            <x-icon-whatsapp class="h-5 w-5" /> Hubungi via WhatsApp
        </x-button>
    </div>
</x-layouts.tenant>
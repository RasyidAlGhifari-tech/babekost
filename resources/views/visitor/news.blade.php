<x-layouts.visitor title="News">
    <x-slot:action>
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-tint-gold text-gold-700">
            <x-heroicon-o-megaphone class="h-5 w-5" />
        </span>
    </x-slot:action>

    <div class="flex flex-col gap-4 p-4">

        {{-- Tab --}}
        <div class="flex rounded-lg bg-segmented p-1 text-xs font-medium">
            @foreach (['promo' => 'Promo', 'agenda' => 'Agenda santunan'] as $key => $label)
                <a href="{{ route('visitor.news', ['tab' => $key]) }}"
                    class="flex-1 rounded-md py-2 text-center {{ $tab === $key ? 'bg-white text-ink shadow-segment' : 'text-slate' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($tab === 'promo')
            @forelse ($promos as $promo)
                <x-card class="space-y-2 p-3">
                    <div class="flex h-28 items-center justify-center rounded-lg" style="background: var(--grad-photo)">
                        <x-heroicon-o-megaphone class="h-6 w-6 text-gold-500" />
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold">{{ $promo->title }}</p>
                        <x-badge status="aktif">Aktif</x-badge>
                    </div>
                    @if ($promo->description)
                        <p class="text-[11px] text-slate">{{ $promo->description }}</p>
                    @endif
                    <x-button href="https://wa.me/{{ config('babekost.whatsapp.number') }}?text={{ urlencode('Halo, saya tertarik dengan promo ' . $promo->title) }}">
                        <x-icon-whatsapp class="h-5 w-5" /> Hubungi via WhatsApp
                    </x-button>
                </x-card>
            @empty
                <p class="py-10 text-center text-sm text-slate">Belum ada promo aktif.</p>
            @endforelse
        @else
            @forelse ($upcoming as $agenda)
                <x-card class="space-y-1 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold">{{ $agenda->title }}</p>
                        <x-badge status="mendatang">Mendatang</x-badge>
                    </div>
                    <p class="text-[11px] text-slate">{{ \Carbon\Carbon::parse($agenda->agenda_date)->translatedFormat('d F Y') }}</p>
                    @if ($agenda->description)
                        <p class="text-[11px] text-slate">{{ $agenda->description }}</p>
                    @endif
                </x-card>
            @empty
                <p class="py-6 text-center text-sm text-slate">Belum ada agenda mendatang.</p>
            @endforelse

            <h3 class="text-sm font-semibold">Dokumentasi sebelumnya</h3>
            @forelse ($past as $agenda)
                <x-card class="space-y-2 p-3">
                    <div>
                        <p class="text-sm font-semibold">{{ $agenda->title }}</p>
                        <p class="text-[11px] text-slate">{{ \Carbon\Carbon::parse($agenda->agenda_date)->translatedFormat('d M Y') }}</p>
                    </div>
                    @if ($agenda->documentation_path)
                        <img src="{{ asset('storage/' . $agenda->documentation_path) }}" alt="{{ $agenda->title }}"
                            loading="lazy" class="h-28 w-full rounded-lg object-cover">
                    @endif
                </x-card>
            @empty
                <p class="py-6 text-center text-sm text-slate">Belum ada dokumentasi.</p>
            @endforelse
        @endif
    </div>
</x-layouts.visitor>
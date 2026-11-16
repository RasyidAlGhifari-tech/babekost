<x-layouts.visitor :title="config('babekost.name')" :logo="true">

    <div class="space-y-5 p-4">

        {{-- Foto utama --}}
        <div>
            <div class="flex h-52 items-center justify-center rounded-xl" style="background: var(--grad-photo)">
                <x-heroicon-o-photo class="h-8 w-8 text-gold-500" />
            </div>
            <div class="mt-2 flex justify-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-gold-700"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-handle"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-handle"></span>
            </div>
        </div>

        {{-- Nama dan alamat --}}
        <div>
            <h2 class="text-base font-bold">{{ $building?->name ?? config('babekost.name') }}</h2>
            <p class="mt-0.5 flex items-start gap-1 text-xs text-slate">
                <x-heroicon-o-map-pin class="mt-0.5 h-4 w-4 shrink-0 text-gold-700" />
                {{ $building?->address ?? '-' }}
            </p>
        </div>

        {{-- Info singkat --}}
        <div class="grid grid-cols-2 gap-3">
            <x-card class="p-3">
                <p class="text-[11px] text-slate">Harga mulai</p>
                <p class="mt-0.5 text-sm font-bold">
                    {{ $startPrice ? 'Rp ' . number_format($startPrice, 0, ',', '.') . '/thn' : '-' }}
                </p>
            </x-card>
            <x-card class="p-3">
                <p class="text-[11px] text-slate">Kamar tersedia</p>
                <p class="mt-0.5 text-sm font-bold">{{ $availableCount }} dari {{ $totalRooms }}</p>
            </x-card>
        </div>

        <x-button href="{{ config('babekost.whatsapp.url') }}">
            <x-icon-whatsapp class="h-5 w-5" /> Hubungi via WhatsApp
        </x-button>

        {{-- Aksi cepat --}}
        <section>
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold">Aksi cepat</h3>
                <a href="{{ route('visitor.rooms') }}" class="text-xs font-medium text-gold-700">Lihat semua</a>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @foreach ($quickRooms as $room)
                    <a href="{{ route('visitor.rooms.show', $room) }}">
                        <x-card class="p-2">
                            <div class="flex h-20 items-center justify-center rounded-lg" style="background: var(--grad-photo)">
                                <x-heroicon-o-photo class="h-6 w-6 text-gold-500" />
                            </div>
                            <p class="mt-2 text-xs font-semibold">Kamar {{ $room->number }}</p>
                            <p class="text-[11px] text-slate">Rp {{ number_format($room->price_yearly, 0, ',', '.') }}/thn</p>
                        </x-card>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Fasilitas --}}
        <section>
            <h3 class="mb-2 text-sm font-semibold">Fasilitas</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($facilities as $f)
                    <span class="rounded-full border border-line-chip bg-white px-3 py-1 text-xs">{{ $f }}</span>
                @endforeach
            </div>
        </section>

        {{-- Lokasi --}}
        @php
            $lat = config('babekost.location.lat');
            $lng = config('babekost.location.lng');
        @endphp
        <section>
            <h3 class="mb-2 text-sm font-semibold">Lokasi</h3>
            <div class="overflow-hidden rounded-xl ring-1 ring-line">
                <iframe
                    src="https://www.google.com/maps?q={{ $lat }},{{ $lng }}&z=18&output=embed"
                    class="h-40 w-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi {{ config('babekost.name') }}"></iframe>
            </div>
            <a href="{{ $building?->maps_url ?? 'https://www.google.com/maps/search/?api=1&query=' . $lat . ',' . $lng }}"
                target="_blank" rel="noopener"
                class="mt-2 flex items-center gap-1 text-xs font-medium text-gold-700">
                <x-heroicon-o-map-pin class="h-4 w-4" /> Buka di Google Maps
            </a>
        </section>

        {{-- News --}}
        <section class="space-y-2">
            @foreach ($promos as $promo)
                <a href="{{ route('visitor.news') }}" class="block">
                    <x-card class="flex items-center gap-3 p-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                            <x-heroicon-o-megaphone class="h-5 w-5" />
                        </span>
                        <div class="flex-1">
                            <p class="text-xs font-semibold">{{ $promo->title }}</p>
                            <p class="text-[11px] text-slate">Promo aktif</p>
                        </div>
                        <x-heroicon-o-chevron-right class="h-4 w-4 text-muted" />
                    </x-card>
                </a>
            @endforeach

            @foreach ($agendas as $agenda)
                <a href="{{ route('visitor.news', ['tab' => 'agenda']) }}" class="block">
                    <x-card class="flex items-center gap-3 p-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                            <x-heroicon-o-heart class="h-5 w-5" />
                        </span>
                        <div class="flex-1">
                            <p class="text-xs font-semibold">{{ $agenda->title }}</p>
                            <p class="text-[11px] text-slate">{{ \Carbon\Carbon::parse($agenda->agenda_date)->translatedFormat('d M Y') }}</p>
                        </div>
                        <x-heroicon-o-chevron-right class="h-4 w-4 text-muted" />
                    </x-card>
                </a>
            @endforeach
        </section>

    </div>
</x-layouts.visitor>
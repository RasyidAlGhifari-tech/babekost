<x-layouts.visitor title="Rooms">
    <div class="space-y-4 p-4">

        <a href="{{ route('visitor.rooms') }}" class="flex items-center gap-1 text-xs font-medium text-gold-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" /> Kembali ke daftar
        </a>

        <div class="flex h-52 items-center justify-center rounded-xl" style="background: var(--grad-photo)">
            <x-heroicon-o-photo class="h-8 w-8 text-gold-500" />
        </div>

        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold">Kamar {{ $room->number }}</h2>
            @if ($room->special_status !== 'normal')
                <x-badge status="nonaktif">{{ ucfirst($room->special_status) }}</x-badge>
            @elseif ($isAvailable)
                <x-badge status="tersedia">Tersedia</x-badge>
            @else
                <x-badge status="terisi">Terisi</x-badge>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3">
            <x-card class="p-3">
                <p class="text-[11px] text-slate">Kapasitas</p>
                <p class="mt-0.5 text-sm font-bold">{{ $room->capacity ?? 1 }} orang</p>
            </x-card>
            <x-card class="p-3">
                <p class="text-[11px] text-slate">Harga</p>
                <p class="mt-0.5 text-sm font-bold">Rp {{ number_format($room->price_yearly, 0, ',', '.') }}/thn</p>
            </x-card>
        </div>

        <section>
            <h3 class="mb-2 text-sm font-semibold">Fasilitas kamar</h3>
            <x-card class="divide-y divide-line p-0">
                @forelse ($room->facilities as $f)
                    <div class="flex items-center gap-2 px-3 py-2.5 text-xs">
                        <span class="font-medium">{{ $f->name }}</span>
                        <span class="text-muted">• {{ $f->pivot->quantity }}</span>
                        @if ($f->pivot->condition)
                            <span class="text-muted">• {{ $f->pivot->condition }}</span>
                        @endif
                    </div>
                @empty
                    <p class="px-3 py-3 text-xs text-slate">Belum ada data fasilitas.</p>
                @endforelse
            </x-card>
        </section>

        <div class="space-y-2">
            @guest
                <x-button href="{{ route('login') }}">
                    <x-heroicon-o-arrow-right-end-on-rectangle class="h-5 w-5" /> Login untuk booking
                </x-button>
            @else
                @if (auth()->user()->isTenant())
                    @if ($room->special_status === 'normal')
                        <x-button href="{{ route('tenant.booking', ['room' => $room->id]) }}">Booking kamar ini</x-button>
                    @else
                        <p class="rounded-lg bg-tint-ink p-3 text-center text-xs text-slate">
                            Kamar ini sedang tidak bisa dipesan.
                        </p>
                    @endif
                @endif
            @endguest

            <x-button variant="outline" href="https://wa.me/{{ config('babekost.whatsapp.number') }}?text={{ urlencode('Halo, saya tertarik dengan Kamar ' . $room->number) }}">
                <x-icon-whatsapp class="h-5 w-5" /> Hubungi via WhatsApp
            </x-button>
        </div>
    </div>
</x-layouts.visitor>
<x-layouts.visitor title="Rooms">
    <div class="space-y-3 p-4">

        {{-- Cari --}}
        <form method="GET" action="{{ route('visitor.rooms') }}">
            @if ($filter !== 'semua')
                <input type="hidden" name="status" value="{{ $filter }}">
            @endif
            <div class="flex items-center gap-2 rounded-lg border border-line-chip bg-white px-3 focus-within:border-gold-700">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 shrink-0 text-muted" />
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari nomor kamar"
                    class="w-full border-0 bg-transparent py-3 text-sm placeholder:text-muted focus:ring-0">
            </div>
        </form>

        {{-- Filter --}}
        <div class="flex gap-2">
            @foreach (['semua' => 'Semua', 'kosong' => 'Kosong', 'terisi' => 'Terisi'] as $key => $label)
                @php $active = $filter === $key; @endphp
                <a href="{{ route('visitor.rooms', array_filter(['status' => $key === 'semua' ? null : $key, 'q' => $search ?: null])) }}"
                    class="rounded-full border px-3 py-1 text-xs font-medium
                    {{ $active ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white text-slate' }}">
                    {{ $label }} {{ $key === 'semua' ? $counts['semua'] : '' }}
                </a>
            @endforeach
        </div>

        {{-- Daftar kamar --}}
        <div class="flex flex-col gap-4">
            @forelse ($rooms as $room)
                @php $available = isset($availableIds[$room->id]); @endphp
                <a href="{{ route('visitor.rooms.show', $room) }}" class="block">
                    <x-card class="flex items-center gap-3 p-3">
                        <x-photo :url="$room->cover_url" :alt="'Kamar ' . $room->number" icon="h-5 w-5"
                            class="h-14 w-14 shrink-0 rounded-lg" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold">Kamar {{ $room->number }}</p>
                            <p class="text-[11px] text-slate">
                                Kapasitas {{ $room->capacity ?? 1 }} • Rp {{ number_format($room->price_yearly, 0, ',', '.') }}/thn
                            </p>
                            <div class="mt-1">
                                @if ($room->special_status !== 'normal')
                                    <x-badge status="nonaktif">{{ ucfirst($room->special_status) }}</x-badge>
                                @elseif ($available)
                                    <x-badge status="tersedia">Tersedia</x-badge>
                                @else
                                    <x-badge status="terisi">Terisi</x-badge>
                                @endif
                            </div>
                        </div>
                        <x-heroicon-o-chevron-right class="h-4 w-4 text-muted" />
                    </x-card>
                </a>
            @empty
                <p class="py-10 text-center text-sm text-slate">Kamar tidak ditemukan.</p>
            @endforelse
        </div>
    </div>
</x-layouts.visitor>
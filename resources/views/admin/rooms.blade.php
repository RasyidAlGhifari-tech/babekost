<x-layouts.admin title="Rooms">
    <div class="flex flex-col gap-3 p-4">

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if ($errors->has('room'))
            <div class="rounded-lg bg-red-50 p-3 text-xs text-red-700">{{ $errors->first('room') }}</div>
        @endif

        <a href="{{ route('admin.rooms.create') }}"
            class="flex items-center justify-center gap-1 rounded-xl bg-gold-700 py-3 text-sm font-semibold text-white">
            <x-heroicon-o-plus class="h-5 w-5" /> Tambah Kamar
        </a>

        <form method="GET" action="{{ route('admin.rooms') }}" class="flex flex-col gap-3">
            @if ($filter !== 'semua') <input type="hidden" name="status" value="{{ $filter }}"> @endif
            <div class="flex items-center gap-2 rounded-lg border border-line-chip bg-white px-3 focus-within:border-gold-700">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 shrink-0 text-muted" />
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari nomor kamar"
                    class="w-full border-0 bg-transparent py-3 text-sm placeholder:text-muted focus:ring-0">
            </div>
            <select name="building" onchange="this.form.submit()"
                class="w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <option value="">Bangunan: semua</option>
                @foreach ($buildings as $b)
                    <option value="{{ $b->id }}" @selected($bid === $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </form>

        <div class="flex gap-2 overflow-x-auto pb-1 text-xs font-medium">
            @foreach (['semua' => 'Semua', 'kosong' => 'Kosong', 'terisi' => 'Terisi', 'terbooking' => 'Terbooking'] as $key => $label)
                <a href="{{ route('admin.rooms', array_filter(['status' => $key === 'semua' ? null : $key, 'q' => $search ?: null, 'building' => $bid ?: null])) }}"
                    class="shrink-0 rounded-full border px-3 py-1 {{ $filter === $key ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white text-slate' }}">
                    {{ $label }} {{ $counts[$key] }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-col gap-3">
            @forelse ($rooms as $room)
                @php
                    $active = $room->bookings->first();
                    $daysLeft = $active ? (int) today()->diffInDays($active->end_date, false) : null;
                @endphp
                <x-card class="flex items-center gap-3 overflow-visible p-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg" style="background: var(--grad-photo)">
                        <x-heroicon-o-photo class="h-5 w-5 text-gold-500" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold">Kamar {{ $room->number }}</p>
                        <p class="text-[11px] text-slate">
                            {{ $room->building->name }} • Kapasitas {{ $room->capacity }} • Rp {{ number_format($room->price_yearly, 0, ',', '.') }}/thn
                        </p>
                        <div class="mt-1 flex flex-wrap gap-1">
                            @if ($room->special_status !== 'normal')
                                <x-badge status="nonaktif">{{ ucfirst($room->special_status) }}</x-badge>
                            @elseif (isset($terisiIds[$room->id]))
                                <x-badge status="aktif">Terisi</x-badge>
                                @if ($daysLeft !== null && $daysLeft <= 14)
                                    <x-badge status="ditolak">Sisa {{ $daysLeft }} hari</x-badge>
                                @endif
                            @elseif (isset($bookIds[$room->id]))
                                <x-badge status="terbooking">Terbooking</x-badge>
                            @else
                                <x-badge status="kosong">Kosong</x-badge>
                            @endif
                        </div>
                    </div>

                    {{-- Menu titik tiga --}}
                    <div class="relative shrink-0 self-start -mr-1 -mt-1" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="rounded-full p-2 text-slate hover:bg-tint-gold">
                            <x-heroicon-o-ellipsis-vertical class="h-5 w-5" />
                        </button>
                        <div x-show="open" x-cloak x-transition
                            class="absolute right-0 z-20 mt-1 w-44 overflow-hidden rounded-xl bg-white text-xs shadow-lg ring-1 ring-line">
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="block px-4 py-3 hover:bg-tint-gold">Ubah</a>

                            @foreach (['normal' => 'Aktifkan (normal)', 'perbaikan' => 'Tandai perbaikan', 'nonaktif' => 'Nonaktifkan'] as $v => $l)
                                @if ($room->special_status !== $v)
                                    <form method="POST" action="{{ route('admin.rooms.status', $room) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="special_status" value="{{ $v }}">
                                        <button class="block w-full px-4 py-3 text-left hover:bg-tint-gold">{{ $l }}</button>
                                    </form>
                                @endif
                            @endforeach

                            <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                                onsubmit="return confirm('Hapus Kamar {{ $room->number }}?')">
                                @csrf @method('DELETE')
                                <button class="block w-full px-4 py-3 text-left text-red-600 hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </div>
                </x-card>
            @empty
                <p class="py-10 text-center text-sm text-slate">Kamar tidak ditemukan.</p>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
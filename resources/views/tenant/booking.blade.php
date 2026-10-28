@php
    $tab = request('tab', 'ajukan');
    $map = [
        'menunggu_pembayaran' => ['menunggu', 'Menunggu bayar'],
        'booking'             => ['terbooking', 'Dibooking'],
        'aktif'               => ['aktif', 'Aktif'],
        'selesai'             => ['nonaktif', 'Selesai'],
        'dibatalkan'          => ['ditolak', 'Dibatalkan'],
    ];
    $pkg = ['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', 'yearly' => 'Tahunan'];
@endphp
<x-layouts.tenant title="Booking">
    <div class="flex flex-col gap-4 p-4">
        <x-tabs :tabs="['ajukan' => 'Ajukan', 'saya' => 'Booking saya']" :active="$tab" />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        @if ($tab === 'ajukan')
            <form method="POST" action="{{ route('tenant.booking.store') }}" class="flex flex-col gap-4"
                x-data="{
                    rooms: @js($rooms),
                    roomId: @js((int) old('room_id', $rooms[0]['id'] ?? 0)),
                    pkg: @js(old('package', 'monthly')),
                    start: @js(old('start_date', today()->toDateString())),
                    get room() { return this.rooms.find(r => r.id == this.roomId) },
                    get end() {
                        if (!this.start) return '-';
                        const d = new Date(this.start + 'T00:00:00');
                        this.pkg === 'yearly' ? d.setFullYear(d.getFullYear() + 1) : d.setMonth(d.getMonth() + 1);
                        d.setDate(d.getDate() - 1);
                        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                    },
                    get total() { return this.room ? (this.pkg === 'yearly' ? this.room.yearly : this.room.monthly) : 0 },
                    rp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID') }
                }">
                @csrf

                <select name="room_id" x-model="roomId" class="w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-sm focus:border-gold-700 focus:ring-0">
                    <template x-for="r in rooms" :key="r.id">
                        <option :value="r.id" x-text="r.label"></option>
                    </template>
                </select>

                <x-card class="flex items-center gap-3 p-3" x-show="room">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg" style="background: var(--grad-photo)">
                        <x-heroicon-o-photo class="h-6 w-6 text-gold-500" />
                    </div>
                    <div>
                        <p class="text-sm font-bold" x-text="room?.label"></p>
                        <p class="text-[11px] text-slate" x-text="'Kapasitas ' + room?.capacity + ' • ' + rp(room?.yearly) + '/thn'"></p>
                    </div>
                </x-card>

                <input type="hidden" name="package" :value="pkg">
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                    <button type="button" @click="pkg = 'monthly'"
                        :class="pkg === 'monthly' ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white'"
                        class="rounded-full border py-2.5">Bulanan</button>
                    <button type="button" @click="pkg = 'yearly'"
                        :class="pkg === 'yearly' ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white'"
                        class="rounded-full border py-2.5">Tahunan</button>
                </div>

                <div>
                    <p class="mb-1 text-[11px] text-slate">Tanggal mulai</p>
                    <input type="date" name="start_date" x-model="start" min="{{ today()->toDateString() }}"
                        class="w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                </div>
                <div>
                    <p class="mb-1 text-[11px] text-slate">Tanggal berakhir (otomatis)</p>
                    <div class="rounded-lg border border-line-chip bg-tint-ink px-3 py-3 text-xs text-slate" x-text="end"></div>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-ink px-4 py-4 text-white">
                    <span class="text-xs">Total biaya</span>
                    <span class="text-xl font-bold text-gold-300" x-text="rp(total)"></span>
                </div>

                <x-house-rules />

                <x-button type="submit">Ajukan booking</x-button>
            </form>
        @else
            @forelse ($bookings as $b)
                @php [$st, $label] = $map[$b->status] ?? ['default', $b->status]; @endphp
                <x-card class="space-y-1 p-3">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-semibold text-gold-700">
                            Kamar {{ $b->room->number }} • {{ $pkg[$b->package] ?? $b->package }}
                        </p>
                        <x-badge :status="$st">{{ $label }}</x-badge>
                    </div>
                    <p class="text-[11px] text-slate">
                        {{ $b->start_date->translatedFormat('j M Y') }} - {{ $b->end_date->translatedFormat('j M Y') }}
                    </p>
                    @if ($b->status === 'menunggu_pembayaran')
                        <a href="{{ route('tenant.payment', ['booking' => $b->id]) }}"
                            class="flex items-center gap-1 text-[11px] font-semibold text-gold-700">
                            Lanjut bayar <x-heroicon-o-chevron-right class="h-3 w-3" />
                        </a>
                    @endif
                </x-card>
            @empty
                <p class="py-10 text-center text-sm text-slate">Belum ada booking.</p>
            @endforelse
        @endif
    </div>
</x-layouts.tenant>
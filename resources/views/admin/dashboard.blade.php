@php
    $pm = [
        'menunggu_verifikasi' => ['menunggu', 'Menunggu'],
        'terverifikasi'       => ['terverifikasi', 'Terverifikasi'],
        'ditolak'             => ['ditolak', 'Ditolak'],
    ];
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp
<x-layouts.admin title="Dashboard">
    <div class="flex flex-col gap-4 p-4">

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold">Ringkasan Kos</h2>
            <span class="rounded-full border border-line-chip bg-white px-3 py-1 text-[11px] font-medium text-gold-700">
                {{ today()->translatedFormat('F Y') }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3">
            @foreach ([
                ['Total Kamar', $total,      'building-office',  'bg-tint-gold text-gold-700'],
                ['Kosong',      $kosong,     'key',              'bg-tint-success text-success'],
                ['Terisi',      $terisi,     'user',             'bg-tint-ink text-ink'],
                ['Terbooking',  $terbooking, 'calendar-days',    'bg-tint-warning text-warning'],
            ] as [$label, $val, $icon, $tone])
                <x-card class="flex flex-col items-center gap-1 p-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $tone }}">
                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-5 w-5" />
                    </span>
                    <p class="text-2xl font-bold">{{ $val }}</p>
                    <p class="text-[11px] text-slate">{{ $label }}</p>
                </x-card>
            @endforeach
        </div>

        <x-card class="p-3">
            <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-gold-700">Tingkat hunian</span>
                <span class="font-bold">{{ $occupancy }}%</span>
            </div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-track">
                <div class="h-full rounded-full" style="width: {{ $occupancy }}%; background: var(--grad-progress)"></div>
            </div>
        </x-card>

        {{-- Keuangan --}}
        <section>
            <h3 class="mb-2 text-sm font-semibold">Keuangan bulan ini</h3>
            <div class="rounded-xl bg-ink p-4 text-white">
                <div class="flex items-center justify-between text-[11px] text-on-ink-soft">
                    <span>Pemasukan</span>
                    <span class="text-white">{{ $rp($months->last()['sum']) }}</span>
                </div>
                <div class="mt-3 flex h-20 items-end gap-2 border-b border-white/15 pb-1">
                    @foreach ($months as $m)
                        <div class="flex-1 rounded-t {{ $loop->last ? 'bg-gold-300' : 'bg-gold-500' }}"
                             style="height: {{ max(8, (int) round($m['sum'] / $max * 100)) }}%"></div>
                    @endforeach
                </div>
                <div class="mt-1 flex gap-2 text-center text-[10px]">
                    @foreach ($months as $m)
                        <span class="flex-1 {{ $loop->last ? 'font-bold text-gold-300' : 'text-on-ink-muted' }}">{{ $m['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Perlu tindakan --}}
        <section class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold">Perlu Tindakan</h3>
                <a href="{{ route('admin.finance') }}" class="text-xs font-medium text-gold-700">Lihat semua</a>
            </div>
            @foreach ([
                ['Bukti transfer menunggu', 'Perlu diverifikasi',      'document-text',        $todo['pending'],    'bg-danger text-white'],
                ['Masa sewa berakhir ≤ 7 hari', 'Segera hubungi penyewa', 'calendar-days',      $todo['ending'],     'bg-tint-warning text-warning'],
                ['Pengingat booking H + 1', 'Mulai besok',             'clock',                $todo['tomorrow'],   'bg-tint-ink text-ink'],
                ['Keluhan baru',            'Belum ditangani',         'chat-bubble-left-right', $todo['complaints'], 'bg-ink text-white'],
            ] as [$t, $s, $icon, $count, $badge])
                <x-card class="flex items-center gap-3 p-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <p class="text-xs font-semibold">{{ $t }}</p>
                        <p class="text-[11px] text-slate">{{ $s }}</p>
                    </div>
                    <span class="flex h-6 min-w-6 items-center justify-center rounded-full px-2 text-[11px] font-bold {{ $badge }}">{{ $count }}</span>
                </x-card>
            @endforeach
        </section>

        {{-- Pembayaran terbaru --}}
        <section class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold">Pembayaran terbaru</h3>
                <a href="{{ route('admin.finance') }}" class="text-xs font-medium text-gold-700">Finance</a>
            </div>
            @forelse ($recent as $p)
                @php [$st, $label] = $pm[$p->status]; @endphp
                <x-card class="flex items-center justify-between p-3">
                    <div>
                        <p class="text-xs font-semibold">Kamar {{ $p->booking->room->number }}</p>
                        <p class="text-[11px] text-slate">{{ $rp($p->amount) }} • {{ $p->paid_at->translatedFormat('j M') }}</p>
                    </div>
                    <x-badge :status="$st">{{ $label }}</x-badge>
                </x-card>
            @empty
                <p class="py-4 text-center text-xs text-slate">Belum ada pembayaran.</p>
            @endforelse
        </section>

        {{-- Aksi cepat --}}
        <section>
            <h3 class="mb-2 text-sm font-semibold">Aksi cepat</h3>
            <div class="grid grid-cols-3 gap-3">
                @foreach ([
                    ['Pengeluaran', 'plus',      route('admin.finance', ['tab' => 'pengeluaran'])],
                    ['Kamar',       'icon-bed',  route('admin.rooms')],
                    ['Promo',       'megaphone', '#'],
                ] as [$label, $icon, $href])
                    <a href="{{ $href }}" class="block">
                        <x-card class="flex flex-col items-center gap-2 p-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                                <x-dynamic-component :component="str_starts_with($icon, 'icon-') ? $icon : 'heroicon-o-' . $icon" class="h-5 w-5" />
                            </span>
                            <span class="text-[11px] font-semibold">{{ $label }}</span>
                        </x-card>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.admin>
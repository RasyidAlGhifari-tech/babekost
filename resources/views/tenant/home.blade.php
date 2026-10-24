@php
    $packageLabel = ['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', 'yearly' => 'Tahunan'];
    $paymentMap = [
        'menunggu_verifikasi' => ['menunggu', 'Menunggu'],
        'terverifikasi'       => ['terverifikasi', 'Terverifikasi'],
        'ditolak'             => ['ditolak', 'Ditolak'],
    ];
    $complaintMap = [
        'baru'     => ['baru', 'Baru'],
        'diproses' => ['menunggu', 'Diproses'],
        'selesai'  => ['aktif', 'Selesai'],
    ];
@endphp
<x-layouts.tenant title="Home" :greeting="'Halo, ' . auth()->user()->name">
    <div class="flex flex-col gap-4 p-4">

        {{-- Kamar aktif --}}
        @if ($booking)
            <x-card class="space-y-3 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg" style="background: var(--grad-photo)">
                        <x-heroicon-o-photo class="h-6 w-6 text-gold-500" />
                    </div>
                    <div>
                        <p class="text-base font-bold">Kamar {{ $booking->room->number }}</p>
                        <p class="text-[11px] text-slate">{{ $booking->room->building->name }}</p>
                        <p class="text-[11px] text-slate">{{ $packageLabel[$booking->package] ?? $booking->package }}</p>
                    </div>
                </div>
                <p class="text-[11px] text-slate">
                    {{ $booking->start_date->translatedFormat('j M Y') }} -
                    {{ $booking->end_date->translatedFormat('j M Y') }}
                </p>
                <div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate">Sisa masa sewa</span>
                        <span class="font-semibold">{{ $daysLeft }} Hari</span>
                    </div>
                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-track">
                        <div class="h-full rounded-full" style="width: {{ $progress }}%; background: var(--grad-progress)"></div>
                    </div>
                </div>
            </x-card>
        @else
            <x-card class="space-y-3 p-4 text-center">
                <p class="text-sm font-semibold">Belum ada kamar aktif</p>
                <p class="text-xs text-slate">Ajukan booking untuk mulai menyewa kamar.</p>
                <x-button href="{{ route('tenant.booking') }}">Ajukan booking</x-button>
            </x-card>
        @endif

        {{-- Pembayaran terakhir --}}
        @if ($lastPayment)
            @php [$pSt, $pLabel] = $paymentMap[$lastPayment->status] ?? ['default', $lastPayment->status]; @endphp
            <x-card class="flex items-center justify-between p-3">
                <div>
                    <p class="text-xs font-semibold">Pembayaran terakhir</p>
                    <p class="text-[11px] text-slate">
                        Rp {{ number_format($lastPayment->amount, 0, ',', '.') }} • {{ $lastPayment->paid_at->translatedFormat('j M Y') }}
                    </p>
                </div>
                <x-badge :status="$pSt">{{ $pLabel }}</x-badge>
            </x-card>
        @endif

        {{-- Aksi cepat --}}
        <div class="grid grid-cols-3 gap-3">
            @foreach ([
                ['Upload proof', 'arrow-up-tray',      'bg-tint-gold text-gold-700',   'tenant.payment'],
                ['Report issue', 'exclamation-circle', 'bg-tint-danger text-danger',   'tenant.complaints'],
                ['Extend',       'arrow-path',         'bg-tint-success text-success', 'tenant.booking'],
            ] as [$label, $icon, $tone, $route])
                <a href="{{ route($route) }}" class="block">
                    <x-card class="flex flex-col items-center gap-2 p-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ $tone }}">
                            <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-5 w-5" />
                        </span>
                        <span class="text-[11px] font-semibold">{{ $label }}</span>
                    </x-card>
                </a>
            @endforeach
        </div>

        {{-- Keluhan terakhir --}}
        @if ($lastComplaint)
            @php [$cSt, $cLabel] = $complaintMap[$lastComplaint->status] ?? ['default', $lastComplaint->status]; @endphp
            <section class="space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold">Keluhan terakhir</h3>
                    <a href="{{ route('tenant.complaints', ['tab' => 'riwayat']) }}" class="text-xs font-medium text-gold-700">Lihat semua</a>
                </div>
                <x-card class="flex items-center justify-between gap-3 p-3">
                    <p class="truncate text-xs font-semibold">{{ \Illuminate\Support\Str::limit($lastComplaint->description, 40) }}</p>
                    <x-badge :status="$cSt">{{ $cLabel }}</x-badge>
                </x-card>
            </section>
        @endif

        {{-- Info kos --}}
        <section class="space-y-2">
            <h3 class="text-sm font-semibold">Info kos</h3>

            @if ($promo)
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
            @endif

            @if ($agenda)
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
            @endif
        </section>

        <x-button href="https://wa.me/6281222223215">
            <x-icon-whatsapp class="h-5 w-5" /> Hubungi via WhatsApp
        </x-button>
    </div>
</x-layouts.tenant>
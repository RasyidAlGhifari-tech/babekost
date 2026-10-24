@php $pkg = ['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', 'yearly' => 'Tahunan']; @endphp
<x-layouts.admin title="Bookings">
    <div class="flex flex-col gap-3 p-4">
        <x-back />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif

        <div class="flex gap-2 text-xs font-medium">
            @foreach (['pending' => 'Pending', 'aktif' => 'Aktif', 'selesai' => 'Selesai'] as $key => $label)
                <a href="{{ route('admin.bookings', ['tab' => $key]) }}"
                   class="rounded-full border px-3 py-1 {{ $tab === $key ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white text-slate' }}">
                    {{ $label }} {{ $counts[$key] }}
                </a>
            @endforeach
        </div>

        @forelse ($bookings as $b)
            @php
                $left = (int) today()->diffInDays($b->end_date, false);
                [$st, $label] = match ($b->status) {
                    'menunggu_pembayaran' => ['menunggu', 'Pending'],
                    'booking', 'aktif'    => ['aktif', 'Aktif'],
                    'selesai'             => ['nonaktif', 'Selesai'],
                    default               => ['ditolak', 'Dibatalkan'],
                };
            @endphp
            <x-card class="space-y-2 p-3">
                <div class="flex items-start justify-between">
                    <p class="text-xs font-semibold text-gold-700">Kamar {{ $b->room->number }} • {{ $b->tenant->user->name }}</p>
                    <x-badge :status="$st">{{ $label }}</x-badge>
                </div>
                <p class="text-[11px] text-slate">
                    {{ $pkg[$b->package] ?? $b->package }} • {{ $b->start_date->translatedFormat('j M Y') }} - {{ $b->end_date->translatedFormat('j M Y') }}
                </p>
                @if ($tab === 'aktif' && $left <= 14 && $left >= 0)
                    <x-badge status="ditolak">Sisa {{ $left }} hari</x-badge>
                @endif
                @if ($b->status === 'menunggu_pembayaran')
                    <div class="grid grid-cols-2 gap-2">
                        <form method="POST" action="{{ route('admin.bookings.approve', $b) }}">@csrf <x-button type="submit">Setuju</x-button></form>
                        <form method="POST" action="{{ route('admin.bookings.reject', $b) }}">@csrf <x-button type="submit" variant="danger">Tolak</x-button></form>
                    </div>
                @endif
            </x-card>
        @empty
            <p class="py-10 text-center text-sm text-slate">Tidak ada booking.</p>
        @endforelse
    </div>
</x-layouts.admin>
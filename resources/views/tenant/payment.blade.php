@php
    $tab = request('tab', 'unggah');
    $map = [
        'menunggu_verifikasi' => ['menunggu', 'Menunggu'],
        'terverifikasi'       => ['terverifikasi', 'Terverifikasi'],
        'ditolak'             => ['ditolak', 'Ditolak'],
    ];
    $selected = (int) old('booking_id', request('booking', $bookings->first()?->id));
@endphp
<x-layouts.tenant title="Payment">
    <x-slot:action>
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-tint-gold text-gold-700">
            <x-heroicon-o-receipt-percent class="h-5 w-5" />
        </span>
    </x-slot:action>

    <div class="flex flex-col gap-4 p-4">
        <x-tabs :tabs="['unggah' => 'Unggah', 'riwayat' => 'Riwayat']" :active="$tab" />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        @if ($tab === 'unggah')
            @if ($bookings->isEmpty())
                <p class="py-10 text-center text-sm text-slate">Belum ada booking yang perlu dibayar.</p>
            @else
                <form method="POST" action="{{ route('tenant.payment.store') }}" enctype="multipart/form-data"
                      class="flex flex-col gap-4" x-data="{ file: null }">
                    @csrf
                    <div>
                        <p class="mb-1 text-[11px] text-slate">Pilih booking</p>
                        <select name="booking_id" class="w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                            @foreach ($bookings as $b)
                                <option value="{{ $b->id }}" @selected($b->id === $selected)>
                                    Kamar {{ $b->room->number }} • {{ ['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', 'yearly' => 'Tahunan'][$b->package] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <p class="mb-1 text-[11px] text-slate">Jumlah pembayaran (Rp)</p>
                        <input type="number" name="amount" min="1" value="{{ old('amount', $bookings->firstWhere('id', $selected)?->total_price) }}"
                            class="w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                    </div>
                    <div>
                        <p class="mb-1 text-[11px] text-slate">Bukti transfer</p>
                        <label class="flex h-40 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl bg-tint-gold text-center">
                            <input type="file" name="proof" accept="image/png,image/jpeg" class="hidden"
                                   @change="file = $event.target.files[0]?.name">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-tint-gold text-gold-700">
                                <x-heroicon-o-arrow-up-tray class="h-5 w-5" />
                            </span>
                            <span class="px-3 text-[11px] font-medium text-gold-700"
                                  x-text="file ?? 'Tap untuk unggah bukti transfer'"></span>
                        </label>
                        <p class="mt-1 flex items-center gap-1 text-[10px] text-slate">
                            <x-heroicon-o-information-circle class="h-4 w-4" /> Format JPG atau PNG, maksimal 5 MB
                        </p>
                    </div>
                    <x-button type="submit">Kirim bukti</x-button>
                </form>
            @endif

            <div class="flex items-center justify-between rounded-xl bg-ink px-4 py-4 text-white">
                <div>
                    <p class="text-[11px] text-on-ink-soft">Transfer ke</p>
                    <p class="text-sm font-bold">BCA - 1234 5678 90</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-gold-300">
                    <x-heroicon-o-clipboard-document class="h-5 w-5" />
                </span>
            </div>
        @else
            @forelse ($payments as $p)
                @php [$st, $label] = $map[$p->status] ?? ['default', $p->status]; @endphp
                <x-card class="space-y-1 p-3">
                    <div class="flex items-start justify-between">
                        <p class="text-sm font-semibold">Rp {{ number_format($p->amount, 0, ',', '.') }}</p>
                        <x-badge :status="$st">{{ $label }}</x-badge>
                    </div>
                    <p class="text-[11px] text-slate">
                        Kamar {{ $p->booking->room->number }} • {{ $p->paid_at->translatedFormat('j M Y') }}
                        • {{ $p->type === 'dp' ? 'DP' : 'Pelunasan' }}
                    </p>
                    @if ($p->status === 'ditolak')
                        @if ($p->rejection_reason)
                            <p class="text-[11px] text-danger">{{ $p->rejection_reason }}</p>
                        @endif
                        <a href="{{ route('tenant.payment', ['booking' => $p->booking_id]) }}"
                            class="flex items-center gap-1 text-[11px] font-semibold text-gold-700">
                            <x-heroicon-o-arrow-up-tray class="h-3.5 w-3.5" /> Unggah ulang
                        </a>
                    @endif
                </x-card>
            @empty
                <p class="py-10 text-center text-sm text-slate">Belum ada pembayaran.</p>
            @endforelse
        @endif
    </div>
</x-layouts.tenant>
@php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
<x-layouts.admin title="Finance">
    <div class="flex flex-col gap-4 p-4">
        <x-tabs :tabs="['verifikasi' => 'Verifikasi', 'pengeluaran' => 'Pengeluaran', 'laba' => 'Laba']" :active="$tab" />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        {{-- ================= VERIFIKASI ================= --}}
        @if ($tab === 'verifikasi')
            <div class="flex gap-2 text-xs font-medium">
                @foreach (['menunggu_verifikasi' => 'Menunggu', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $key => $label)
                    <a href="{{ route('admin.finance', ['status' => $key]) }}"
                       class="rounded-full border px-3 py-1 {{ $status === $key ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white text-slate' }}">
                        {{ $label }} {{ $counts[$key] ?? 0 }}
                    </a>
                @endforeach
            </div>

            @forelse ($payments as $p)
                <x-card class="space-y-2 p-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold">Kamar {{ $p->booking->room->number }} • {{ $p->booking->tenant->user->name }}</p>
                            <p class="text-[11px] text-slate">
                                {{ $rp($p->amount) }} • {{ $p->paid_at->translatedFormat('j M Y') }} • {{ $p->type === 'dp' ? 'DP' : 'Pelunasan' }}
                            </p>
                        </div>
                        @if ($p->status === 'menunggu_verifikasi') <x-badge status="menunggu">Menunggu</x-badge>
                        @elseif ($p->status === 'terverifikasi') <x-badge status="terverifikasi">Terverifikasi</x-badge>
                        @else <x-badge status="ditolak">Ditolak</x-badge> @endif
                    </div>

                    <a href="{{ route('admin.finance.proof', $p) }}" target="_blank" class="block overflow-hidden rounded-lg bg-tint-gold">
                        <img src="{{ route('admin.finance.proof', $p) }}" alt="Bukti transfer" loading="lazy" class="h-40 w-full object-cover">
                    </a>

                    @if ($p->status === 'menunggu_verifikasi')
                        <div class="grid grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('admin.finance.approve', $p) }}">@csrf <x-button type="submit">Setujui</x-button></form>
                            <form method="POST" action="{{ route('admin.finance.reject', $p) }}">@csrf <x-button type="submit" variant="danger">Tolak</x-button></form>
                        </div>
                    @elseif ($p->status === 'ditolak' && $p->rejection_reason)
                        <p class="text-[11px] text-danger">{{ $p->rejection_reason }}</p>
                    @elseif ($p->receipt_number)
                        <p class="text-[11px] text-slate">No. kwitansi {{ $p->receipt_number }}</p>
                    @endif
                </x-card>
            @empty
                <p class="py-10 text-center text-sm text-slate">Tidak ada pembayaran.</p>
            @endforelse

        {{-- ================= PENGELUARAN ================= --}}
        @elseif ($tab === 'pengeluaran')
            @php
                $icons = ['air' => 'beaker', 'listrik' => 'bolt', 'perawatan' => 'wrench-screwdriver', 'lainnya' => 'ellipsis-horizontal-circle'];
                $names = ['air' => 'Air', 'listrik' => 'Listrik', 'perawatan' => 'Perawatan', 'lainnya' => 'Lainnya'];
            @endphp

            <div class="grid grid-cols-2 gap-3">
                <x-card class="p-3">
                    <p class="text-[11px] text-slate">Bulan ini</p>
                    <p class="mt-0.5 text-sm font-bold">{{ $rp($total) }}</p>
                </x-card>
                <x-card class="p-3">
                    <p class="text-[11px] text-slate">Bangunan</p>
                    <form method="GET" action="{{ route('admin.finance') }}">
                        <input type="hidden" name="tab" value="pengeluaran">
                        <select name="building" onchange="this.form.submit()" class="w-full border-0 bg-transparent p-0 text-sm font-bold focus:ring-0">
                            <option value="">Semua</option>
                            @foreach ($buildings as $b)
                                <option value="{{ $b->id }}" @selected($bid === $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </x-card>
            </div>

            <details class="rounded-xl bg-gold-700 text-white">
                <summary class="cursor-pointer list-none px-4 py-3 text-center text-sm font-semibold">+ Tambah Pengeluaran</summary>
                <form method="POST" action="{{ route('admin.finance.expenses.store') }}" class="flex flex-col gap-2 bg-white p-3 text-ink">
                    @csrf
                    <select name="building_id" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                        @foreach ($buildings as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                    </select>
                    <select name="category" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                        @foreach ($names as $k => $n) <option value="{{ $k }}">{{ $n }}</option> @endforeach
                    </select>
                    <input type="number" name="amount" min="1" required placeholder="Jumlah (Rp)" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                    <input type="date" name="expense_date" value="{{ today()->toDateString() }}" required class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                    <input name="notes" placeholder="Catatan (opsional)" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                    <x-button type="submit">Simpan</x-button>
                </form>
            </details>

            @forelse ($expenses as $e)
                <x-card class="flex items-center gap-3 p-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-warning text-warning">
                        <x-dynamic-component :component="'heroicon-o-' . $icons[$e->category]" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold">{{ $e->notes ?: $names[$e->category] }}</p>
                        <p class="text-[11px] text-slate">{{ $e->building->name }} • {{ $e->expense_date->translatedFormat('j M') }}</p>
                    </div>
                    <p class="text-xs font-bold">{{ $rp($e->amount) }}</p>
                    <form method="POST" action="{{ route('admin.finance.expenses.destroy', $e) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                        @csrf @method('DELETE')
                        <button class="text-danger" aria-label="Hapus"><x-heroicon-o-trash class="h-4 w-4" /></button>
                    </form>
                </x-card>
            @empty
                <p class="py-8 text-center text-sm text-slate">Belum ada pengeluaran bulan ini.</p>
            @endforelse

        {{-- ================= LABA ================= --}}
        @else
            @php
                $max = max(collect($chart)->max('profit'), 1);
                $qs = ['month' => $month->format('Y-m')];
            @endphp

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.finance', ['tab' => 'laba', 'month' => $month->copy()->subMonth()->format('Y-m')]) }}"
                   class="flex h-8 w-8 items-center justify-center rounded-full bg-white ring-1 ring-line"><x-heroicon-o-chevron-left class="h-4 w-4" /></a>
                <span class="rounded-full border border-line-chip bg-white px-6 py-1.5 text-xs font-semibold">{{ $month->translatedFormat('F Y') }}</span>
                <a href="{{ route('admin.finance', ['tab' => 'laba', 'month' => $month->copy()->addMonth()->format('Y-m')]) }}"
                   class="flex h-8 w-8 items-center justify-center rounded-full bg-white ring-1 ring-line {{ $month->isSameMonth(today()) ? 'pointer-events-none opacity-40' : '' }}">
                    <x-heroicon-o-chevron-right class="h-4 w-4" /></a>
            </div>

            <div class="rounded-xl bg-ink p-4 text-white">
                <div class="flex justify-between text-[11px] text-on-ink-soft"><span>Pemasukan</span><span class="text-white">{{ $rp($income) }}</span></div>
                <div class="mt-1 flex justify-between text-[11px] text-on-ink-soft"><span>Pengeluaran</span><span class="text-white">{{ $rp($expense) }}</span></div>
                <div class="mt-2 flex items-end justify-between border-t border-white/15 pt-2">
                    <span class="text-xs font-semibold text-gold-300">Laba</span>
                    <span class="text-xl font-bold">{{ $rp($profit) }}</span>
                </div>
                <div class="mt-3 flex h-20 items-end gap-2 border-b border-white/15 pb-1">
                    @foreach ($chart as $c)
                        <div class="flex-1 rounded-t {{ $loop->last ? 'bg-gold-300' : 'bg-gold-500' }}"
                             style="height: {{ max(8, (int) round(max($c['profit'], 0) / $max * 100)) }}%"></div>
                    @endforeach
                </div>
                <div class="mt-1 flex gap-2 text-center text-[10px]">
                    @foreach ($chart as $c)
                        <span class="flex-1 {{ $loop->last ? 'font-bold text-gold-300' : 'text-on-ink-muted' }}">{{ $c['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <x-card class="divide-y divide-line p-0">
                @foreach ($perBuilding as $b)
                    <div class="flex items-center justify-between px-3 py-2.5 text-xs">
                        <span>{{ $b['name'] }}</span>
                        <span class="font-semibold">{{ $rp($b['income']) }}</span>
                    </div>
                @endforeach
            </x-card>

            <div class="grid grid-cols-2 gap-2">
                <x-button href="{{ route('admin.finance.export', ['type' => 'pdf'] + $qs) }}">Ekspor PDF</x-button>
                <x-button href="{{ route('admin.finance.export', ['type' => 'excel'] + $qs) }}" variant="outline">Ekspor Excel</x-button>
            </div>
        @endif
    </div>
</x-layouts.admin>
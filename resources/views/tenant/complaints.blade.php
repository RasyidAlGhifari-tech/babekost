@php
    $tab = request('tab', 'ajukan');
    $map = [
        'baru'     => ['baru', 'Baru'],
        'diproses' => ['menunggu', 'Diproses'],
        'selesai'  => ['aktif', 'Selesai'],
    ];
@endphp
<x-layouts.tenant title="Complaints">
    <x-slot:action>
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-tint-gold text-gold-700">
            <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
        </span>
    </x-slot:action>

    <div class="flex flex-col gap-4 p-4">
        <x-tabs :tabs="['ajukan' => 'Ajukan', 'riwayat' => 'Riwayat']" :active="$tab" />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        @if ($tab === 'ajukan')
            <form method="POST" action="{{ route('tenant.complaints.store') }}" class="flex flex-col gap-4">
                @csrf
                <div>
                    <p class="mb-1 text-[11px] text-slate">Kamar</p>
                    <div class="flex items-center justify-between rounded-lg border border-line-chip bg-tint-ink px-3 py-3 text-xs text-slate">
                        {{ $room ? 'Kamar ' . $room->number : 'Belum ada kamar aktif' }}
                        <x-heroicon-o-lock-closed class="h-4 w-4 text-muted" />
                    </div>
                </div>
                <div>
                    <p class="mb-1 text-[11px] text-slate">Keluhan</p>
                    <textarea name="description" rows="5" required
                        placeholder="Jelaskan keluhan kamu, misalnya bagian yang rusak dan sejak kapan terjadi ..."
                        class="w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-xs placeholder:text-muted focus:border-gold-700 focus:ring-0">{{ old('description') }}</textarea>
                </div>
                <x-button type="submit">Ajukan keluhan</x-button>
                <p class="flex items-center gap-1 text-[10px] text-slate">
                    <x-heroicon-o-information-circle class="h-4 w-4" /> Pemilik akan menerima keluhan dan mengubah statusnya.
                </p>
            </form>
        @else
            <div class="flex gap-2 text-xs font-medium">
                @foreach ([null => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $key => $label)
                    <a href="{{ route('tenant.complaints', array_filter(['tab' => 'riwayat', 'status' => $key])) }}"
                        class="rounded-full border px-3 py-1 {{ $filter === $key ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white text-slate' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @forelse ($complaints as $c)
                @php [$st, $label] = $map[$c->status] ?? ['default', $c->status]; @endphp
                <x-card class="flex items-center justify-between gap-3 p-3">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold">{{ \Illuminate\Support\Str::limit($c->description, 40) }}</p>
                        <p class="text-[11px] text-slate">{{ $c->room ? 'Kamar ' . $c->room->number : '-' }} • {{ $c->created_at->translatedFormat('j M Y') }}</p>
                        @if ($c->admin_note)
                            <p class="text-[11px] text-gold-700">Pemilik: {{ $c->admin_note }}</p>
                        @endif
                    </div>
                    <x-badge :status="$st">{{ $label }}</x-badge>
                </x-card>
            @empty
                <p class="py-10 text-center text-sm text-slate">Belum ada keluhan.</p>
            @endforelse
        @endif
    </div>
</x-layouts.tenant>
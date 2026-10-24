<x-layouts.admin title="Promo and Events">
    <div class="flex flex-col gap-3 p-4">
        <x-back />
        <x-tabs :tabs="['promo' => 'Promo', 'events' => 'Events']" :active="$tab" />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif

        @if ($tab === 'promo')
            <form method="POST" action="{{ route('admin.promos.store') }}" class="flex flex-col gap-2">
                @csrf
                <input name="title" required placeholder="Judul promo" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <input name="description" placeholder="Keterangan" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <x-button type="submit">+ Tambah promo</x-button>
            </form>

            @foreach ($promos as $p)
                <x-card class="space-y-2 p-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold">{{ $p->title }}</p>
                            <p class="text-[11px] text-slate">{{ $p->description }}</p>
                        </div>
                        <x-badge :status="$p->is_active ? 'aktif' : 'nonaktif'">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-semibold">
                        <form method="POST" action="{{ route('admin.promos.toggle', $p) }}">@csrf
                            <button class="text-gold-700">{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.promos.destroy', $p) }}" onsubmit="return confirm('Hapus promo?')">@csrf @method('DELETE')
                            <button class="text-danger">Hapus</button>
                        </form>
                    </div>
                </x-card>
            @endforeach
        @else
            <form method="POST" action="{{ route('admin.agendas.store') }}" class="flex flex-col gap-2">
                @csrf
                <input name="title" required placeholder="Judul agenda" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <input type="date" name="agenda_date" required class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <input name="description" placeholder="Keterangan" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <x-button type="submit">+ Tambah agenda</x-button>
            </form>

            @foreach ($agendas as $a)
                <x-card class="space-y-2 p-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold">{{ $a->title }}</p>
                            <p class="text-[11px] text-slate">{{ \Carbon\Carbon::parse($a->agenda_date)->translatedFormat('j F Y') }}</p>
                            @if ($a->description) <p class="text-[11px] text-slate">{{ $a->description }}</p> @endif
                        </div>
                        <x-badge :status="$a->status === 'selesai' ? 'nonaktif' : 'mendatang'">{{ $a->status === 'selesai' ? 'Selesai' : 'Mendatang' }}</x-badge>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-semibold">
                        @if ($a->status === 'rencana')
                            <form method="POST" action="{{ route('admin.agendas.done', $a) }}">@csrf <button class="text-gold-700">Tandai selesai</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.agendas.destroy', $a) }}" onsubmit="return confirm('Hapus agenda?')">@csrf @method('DELETE')
                            <button class="text-danger">Hapus</button>
                        </form>
                    </div>
                </x-card>
            @endforeach
        @endif
    </div>
</x-layouts.admin>
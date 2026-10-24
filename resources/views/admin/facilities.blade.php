<x-layouts.admin title="Facilities">
    <div class="flex flex-col gap-3 p-4">
        <x-back />
        <x-tabs :tabs="['master' => 'Master', 'kamar' => 'Per kamar']" :active="$tab" />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        @if ($tab === 'master')
            @foreach ($facilities as $f)
                <x-card class="p-3">
                    <details>
                        <summary class="flex cursor-pointer list-none items-center gap-3">
                            <div class="flex-1">
                                <p class="text-xs font-semibold">{{ $f->name }}</p>
                                <p class="text-[11px] text-slate">{{ $f->description ?: '-' }}</p>
                            </div>
                            <x-heroicon-o-pencil-square class="h-4 w-4 text-gold-700" />
                        </summary>
                        <form method="POST" action="{{ route('admin.facilities.update', $f) }}" class="mt-3 flex flex-col gap-2">
                            @csrf @method('PUT')
                            <input name="name" value="{{ $f->name }}" required class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                            <input name="description" value="{{ $f->description }}" placeholder="Keterangan" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                            <x-button type="submit">Simpan</x-button>
                        </form>
                        <form method="POST" action="{{ route('admin.facilities.destroy', $f) }}" class="mt-2" onsubmit="return confirm('Hapus fasilitas ini?')">
                            @csrf @method('DELETE')
                            <x-button type="submit" variant="danger">Hapus</x-button>
                        </form>
                    </details>
                </x-card>
            @endforeach

            <form method="POST" action="{{ route('admin.facilities.store') }}" class="flex flex-col gap-2">
                @csrf
                <input name="name" required placeholder="Nama fasilitas baru" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <input name="description" placeholder="Keterangan (opsional)" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <x-button type="submit">+ Tambah fasilitas</x-button>
            </form>
        @else
            <form method="GET" action="{{ route('admin.facilities') }}">
                <input type="hidden" name="tab" value="kamar">
                <select name="room" onchange="this.form.submit()" class="w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                    @foreach ($rooms as $r)
                        <option value="{{ $r->id }}" @selected($room?->id === $r->id)>{{ $r->building->name }} • Kamar {{ $r->number }}</option>
                    @endforeach
                </select>
            </form>

            @if ($room)
                @forelse ($room->facilities as $f)
                    <x-card class="flex items-center gap-3 p-3">
                        <div class="flex-1">
                            <p class="text-xs font-semibold">{{ $f->name }}</p>
                            <p class="text-[11px] text-slate">Jumlah {{ $f->pivot->quantity }}{{ $f->pivot->condition ? ' • ' . $f->pivot->condition : '' }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.facilities.detach', [$room, $f]) }}">
                            @csrf @method('DELETE')
                            <button class="text-danger" aria-label="Lepas"><x-heroicon-o-trash class="h-4 w-4" /></button>
                        </form>
                    </x-card>
                @empty
                    <p class="py-4 text-center text-xs text-slate">Belum ada fasilitas di kamar ini.</p>
                @endforelse

                <form method="POST" action="{{ route('admin.facilities.attach', $room) }}" class="flex flex-col gap-2 rounded-xl border border-gold-border p-3">
                    @csrf
                    <select name="facility_id" class="rounded-lg border border-line-chip bg-white px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                        @foreach ($facilities as $f) <option value="{{ $f->id }}">{{ $f->name }}</option> @endforeach
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="quantity" value="1" min="1" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                        <input name="condition" placeholder="Kondisi (Baik)" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                    </div>
                    <x-button type="submit" variant="outline">+ Tambah fasilitas ke kamar</x-button>
                </form>
            @endif
        @endif
    </div>
</x-layouts.admin>
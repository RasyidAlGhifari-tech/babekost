<x-layouts.admin title="Gallery and Buildings">
    <div class="flex flex-col gap-3 p-4">
        <x-back />
        <x-tabs :tabs="['bangunan' => 'Buildings', 'galeri' => 'Gallery']" :active="$tab" />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-tint-danger p-3 text-xs text-danger">{{ $errors->first() }}</div>
        @endif

        @if ($tab === 'bangunan')
            @foreach ($buildings as $b)
                <x-card class="flex items-center gap-3 p-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                        <x-heroicon-o-building-office class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold">{{ $b->name }}</p>
                        <p class="truncate text-[11px] text-slate">{{ $b->address }}</p>
                        <x-badge status="{{ $b->segment === 'putra' ? 'terbooking' : 'baru' }}">{{ ucfirst($b->segment) }}</x-badge>
                    </div>
                    <form method="POST" action="{{ route('admin.buildings.destroy', $b) }}" onsubmit="return confirm('Hapus bangunan ini?')">
                        @csrf @method('DELETE')
                        <button class="text-danger" aria-label="Hapus"><x-heroicon-o-trash class="h-4 w-4" /></button>
                    </form>
                </x-card>
            @endforeach

            <form method="POST" action="{{ route('admin.buildings.store') }}" class="flex flex-col gap-2">
                @csrf
                <input name="name" required placeholder="Nama bangunan" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <input name="address" required placeholder="Alamat" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                <select name="segment" class="rounded-lg border border-line-chip bg-white px-3 py-3 text-xs focus:border-gold-700 focus:ring-0">
                    <option value="putra">Putra</option><option value="putri">Putri</option>
                </select>
                <x-button type="submit">+ Tambah bangunan</x-button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.photos.store') }}" enctype="multipart/form-data" class="flex flex-col gap-2 rounded-xl border border-gold-border p-3">
                @csrf
                <select name="category" class="rounded-lg border border-line-chip bg-white px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                    <option value="bangunan">Bangunan</option><option value="kamar">Kamar</option><option value="fasilitas">Fasilitas</option>
                </select>
                <select name="room_id" class="rounded-lg border border-line-chip bg-white px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                    <option value="">Kamar (khusus kategori Kamar)</option>
                    @foreach ($rooms as $r) <option value="{{ $r->id }}">Kamar {{ $r->number }}</option> @endforeach
                </select>
                <input name="title" placeholder="Judul foto (opsional)" class="rounded-lg border border-line-chip px-3 py-2 text-xs focus:border-gold-700 focus:ring-0">
                <input type="file" name="photo" accept="image/png,image/jpeg" required class="text-xs">
                <x-button type="submit">+ Unggah foto</x-button>
            </form>

            <div class="grid grid-cols-2 gap-3">
                @forelse ($photos as $p)
                    <div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-track">
                        <img src="{{ asset('storage/' . $p->photo_path) }}" alt="{{ $p->title }}" loading="lazy" class="h-full w-full object-cover">
                        <form method="POST" action="{{ route('admin.photos.destroy', $p) }}" class="absolute right-1 top-1" onsubmit="return confirm('Hapus foto?')">
                            @csrf @method('DELETE')
                            <button class="flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-danger"><x-heroicon-o-trash class="h-4 w-4" /></button>
                        </form>
                        <p class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/55 to-transparent p-2 pt-6 text-[11px] font-medium text-white">
                            {{ $p->title ?: ucfirst($p->category) }}
                        </p>
                    </div>
                @empty
                    <p class="col-span-2 py-6 text-center text-xs text-slate">Belum ada foto.</p>
                @endforelse
            </div>
        @endif
    </div>
</x-layouts.admin>
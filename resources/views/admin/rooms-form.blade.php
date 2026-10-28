@php
    $editing = $room->exists;
    $input = 'w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-sm focus:border-gold-700 focus:ring-0';
@endphp
<x-layouts.admin :title="$editing ? 'Ubah Kamar' : 'Tambah Kamar'">
    <form method="POST" class="flex flex-col gap-4 p-4"
          action="{{ $editing ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <a href="{{ route('admin.rooms') }}" class="flex items-center gap-1 text-xs font-medium text-gold-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" /> Kembali
        </a>

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-xs text-red-700">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <label class="block">
            <span class="mb-1 block text-xs font-medium">Bangunan</span>
            <select name="building_id" class="{{ $input }}" required>
                <option value="">Pilih bangunan</option>
                @foreach ($buildings as $b)
                    <option value="{{ $b->id }}" @selected(old('building_id', $room->building_id) == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </label>

        <div class="grid grid-cols-2 gap-3">
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Nomor kamar</span>
                <input name="number" value="{{ old('number', $room->number) }}" class="{{ $input }}" required>
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Kapasitas</span>
                <input type="number" name="capacity" min="1" max="10" value="{{ old('capacity', $room->capacity) }}" class="{{ $input }}" required>
            </label>
        </div>

        <label class="block">
            <span class="mb-1 block text-xs font-medium">Tipe (opsional)</span>
            <input name="type" value="{{ old('type', $room->type) }}" class="{{ $input }}">
        </label>

        @foreach (['price_monthly' => 'Harga per bulan', 'price_quarterly' => 'Harga per 3 bulan', 'price_yearly' => 'Harga per tahun'] as $name => $label)
            <label class="block">
                <span class="mb-1 block text-xs font-medium">{{ $label }} (Rp)</span>
                <input type="number" name="{{ $name }}" min="0" step="1000" value="{{ old($name, $room->$name) }}" class="{{ $input }}" required>
            </label>
        @endforeach

        <label class="block">
            <span class="mb-1 block text-xs font-medium">Status kamar</span>
            <select name="special_status" class="{{ $input }}">
                @foreach (['normal' => 'Normal (bisa dipesan)', 'perbaikan' => 'Perbaikan', 'nonaktif' => 'Nonaktif'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('special_status', $room->special_status) === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </label>

        <div>
            <span class="mb-2 block text-xs font-medium">Fasilitas</span>
            <div class="flex flex-wrap gap-2">
                @foreach ($facilities as $f)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="facilities[]" value="{{ $f->id }}" class="peer sr-only"
                            @checked(in_array($f->id, old('facilities', $selected)))>
                        <span class="block rounded-full border border-line-chip bg-white px-3 py-1 text-xs peer-checked:border-gold-border peer-checked:bg-tint-gold peer-checked:text-gold-700">
                            {{ $f->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <button class="rounded-xl bg-gold-700 py-3 text-sm font-semibold text-white">
            {{ $editing ? 'Simpan Perubahan' : 'Tambah Kamar' }}
        </button>
    </form>
</x-layouts.admin>
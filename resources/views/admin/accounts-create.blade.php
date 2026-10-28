@php
    $input = 'w-full rounded-lg border border-line-chip bg-white px-3 py-3 text-sm focus:border-gold-700 focus:ring-0';
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp
<x-layouts.admin title="Tambah Penyewa">
    <form method="POST" action="{{ route('admin.accounts.store') }}"
          class="flex flex-col gap-4 p-4" x-data="{ paid: {{ old('mark_paid') ? 'true' : 'false' }} }">
        @csrf

        <a href="{{ route('admin.accounts') }}" class="flex items-center gap-1 text-xs font-medium text-gold-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" /> Kembali
        </a>

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif

        @if (session('credentials'))
            <div class="rounded-lg border border-gold-border bg-tint-gold p-3 text-xs">
                <p class="font-semibold text-gold-700">Akun untuk penghuni (catat sekarang, tidak ditampilkan lagi)</p>
                <p class="mt-1">Email: <span class="font-semibold">{{ session('credentials')['email'] }}</span></p>
                <p>Password: <span class="font-mono font-semibold">{{ session('credentials')['password'] }}</span></p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-xs text-red-700">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <p class="text-sm font-bold">Data penghuni</p>

        <label class="block">
            <span class="mb-1 block text-xs font-medium">Nama lengkap</span>
            <input name="name" value="{{ old('name') }}" class="{{ $input }}" required>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium">Email (untuk login)</span>
            <input type="email" name="email" value="{{ old('email') }}" class="{{ $input }}" required>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium">Nomor HP (opsional)</span>
            <input name="phone" value="{{ old('phone') }}" class="{{ $input }}">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium">Kontak darurat (opsional)</span>
            <input name="emergency_contact" value="{{ old('emergency_contact') }}" class="{{ $input }}">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium">Kampus / pekerjaan (opsional)</span>
            <input name="campus_or_job" value="{{ old('campus_or_job') }}" class="{{ $input }}">
        </label>

        <p class="mt-2 text-sm font-bold">Booking</p>

        <label class="block">
            <span class="mb-1 block text-xs font-medium">Kamar</span>
            <select name="room_id" class="{{ $input }}" required>
                <option value="">Pilih kamar</option>
                @foreach ($rooms as $r)
                    <option value="{{ $r->id }}" @selected(old('room_id') == $r->id)>
                        {{ $r->building->name }} • {{ $r->number }} ({{ $rp($r->price_monthly) }}/bln, {{ $rp($r->price_yearly) }}/thn)
                    </option>
                @endforeach
            </select>
        </label>

        <div class="grid grid-cols-2 gap-3">
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Paket</span>
                <select name="package" class="{{ $input }}">
                    <option value="monthly" @selected(old('package') === 'monthly')>Bulanan</option>
                    <option value="yearly"  @selected(old('package', 'yearly') === 'yearly')>Tahunan</option>
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Tanggal mulai</span>
                <input type="date" name="start_date" value="{{ old('start_date', today()->toDateString()) }}" class="{{ $input }}" required>
            </label>
        </div>

        <label class="block">
            <span class="mb-1 block text-xs font-medium">Harga khusus (opsional, Rp)</span>
            <input type="number" name="custom_price" min="0" step="1000" value="{{ old('custom_price') }}"
                   placeholder="Kosongkan untuk memakai harga kamar" class="{{ $input }}">
        </label>

        <label class="block">
            <span class="mb-1 block text-xs font-medium">Status booking</span>
            <select name="status" class="{{ $input }}">
                <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif (sudah menghuni)</option>
                <option value="booking" @selected(old('status') === 'booking')>Booking (akan masuk)</option>
                <option value="menunggu_pembayaran" @selected(old('status') === 'menunggu_pembayaran')>Menunggu pembayaran</option>
            </select>
        </label>

        <label class="flex items-center gap-2 text-xs font-medium">
            <input type="checkbox" name="mark_paid" value="1" x-model="paid" class="rounded border-line-chip text-gold-700 focus:ring-0">
            Sudah dibayar lunas (catat pembayaran terverifikasi)
        </label>

        <div x-show="paid" x-cloak class="grid grid-cols-2 gap-3">
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Tanggal bayar</span>
                <input type="date" name="paid_at" value="{{ old('paid_at', today()->toDateString()) }}" class="{{ $input }}">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Rekening tujuan</span>
                <select name="destination_account" class="{{ $input }}">
                    <option value="suami" @selected(old('destination_account') === 'suami')>Suami</option>
                    <option value="istri" @selected(old('destination_account') === 'istri')>Istri</option>
                </select>
            </label>
        </div>

        <button class="rounded-xl bg-gold-700 py-3 text-sm font-semibold text-white">Simpan Penyewa</button>
    </form>
</x-layouts.admin>
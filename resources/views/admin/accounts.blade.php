<x-layouts.admin title="Tenant Accounts">
    <div class="flex flex-col gap-3 p-4">
        <x-back />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif
        @if (session('reset'))
            <div class="rounded-lg bg-tint-warning p-3 text-xs text-warning">
                Password baru untuk <b>{{ session('reset')['name'] }}</b>:
                <b class="select-all text-sm">{{ session('reset')['password'] }}</b><br>
                Catat dan berikan ke penyewa. Password ini tidak ditampilkan lagi.
            </div>
        @endif

        <a href="{{ route('admin.accounts.create') }}"
            class="flex items-center justify-center gap-1 rounded-xl bg-gold-700 py-3 text-sm font-semibold text-white">
            <x-heroicon-o-plus class="h-5 w-5" /> Tambah Penyewa
        </a>

        <form method="GET" action="{{ route('admin.accounts') }}">
            <div class="flex items-center gap-2 rounded-lg border border-line-chip bg-white px-3 focus-within:border-gold-700">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 shrink-0 text-muted" />
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama, email, atau HP"
                    class="w-full border-0 bg-transparent py-3 text-sm placeholder:text-muted focus:ring-0">
            </div>
        </form>

        @forelse ($users as $u)
            @php $room = $u->tenant?->bookings->first()?->room; @endphp
            <x-card class="space-y-2 p-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold">{{ $u->name }}</p>
                        <p class="text-[11px] text-slate">{{ $u->email }}{{ $u->phone ? ' • ' . $u->phone : '' }}</p>
                        <p class="text-[11px] text-slate">{{ $room ? 'Kamar ' . $room->number : 'Tanpa kamar' }}</p>
                    </div>
                    @if (! $u->is_active) <x-badge status="nonaktif">Nonaktif</x-badge>
                    @elseif ($room) <x-badge status="aktif">Aktif</x-badge>
                    @else <x-badge status="menunggu">Belum booking</x-badge> @endif
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <form method="POST" action="{{ route('admin.accounts.reset', $u) }}" onsubmit="return confirm('Reset password {{ $u->name }}?')">
                        @csrf <x-button type="submit" variant="outline">Reset password</x-button>
                    </form>
                    <form method="POST" action="{{ route('admin.accounts.toggle', $u) }}">
                        @csrf <x-button type="submit" :variant="$u->is_active ? 'danger' : 'primary'">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-button>
                    </form>
                </div>
            </x-card>
        @empty
            <p class="py-10 text-center text-sm text-slate">Akun tidak ditemukan.</p>
        @endforelse
    </div>
</x-layouts.admin>
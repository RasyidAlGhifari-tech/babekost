<x-layouts.tenant title="Profile">
    <div class="flex flex-col gap-4 p-4">

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif

        <x-card class="flex items-center gap-3 p-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-tint-gold text-gold-700">
                <x-heroicon-o-user class="h-6 w-6" />
            </span>
            <div>
                <p class="text-sm font-bold">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-slate">{{ auth()->user()->email }}</p>
                <x-badge status="aktif">Aktif</x-badge>
            </div>
        </x-card>

        @if ($booking)
            <section>
                <h3 class="mb-2 text-sm font-semibold">Kamar saya</h3>
                <x-card class="divide-y divide-line p-0">
                    @forelse ($booking->room->facilities as $f)
                        <p class="px-3 py-2.5 text-xs">
                            {{ $f->name }} • {{ $f->pivot->quantity }}{{ $f->pivot->condition ? ' • ' . $f->pivot->condition : '' }}
                        </p>
                    @empty
                        <p class="px-3 py-3 text-xs text-slate">Belum ada data fasilitas.</p>
                    @endforelse
                </x-card>
            </section>
        @endif

        <section>
            <h3 class="mb-2 text-sm font-semibold">Pengaturan</h3>
            <div class="flex flex-col gap-2">
                @foreach ([
                    ['Ubah data diri', 'pencil-square', route('tenant.profile.edit')],
                    ['Ubah password',  'lock-closed',   route('tenant.profile.password')],
                    ['Kontak pemilik', 'phone',         route('tenant.profile.contact')],
                ] as [$label, $icon, $href])
                    <a href="{{ $href }}" class="block">
                        <x-card class="flex items-center gap-3 p-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                                <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-5 w-5" />
                            </span>
                            <span class="flex-1 text-xs font-semibold">{{ $label }}</span>
                            <x-heroicon-o-chevron-right class="h-4 w-4 text-muted" />
                        </x-card>
                    </a>
                @endforeach

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left">
                        <x-card class="flex items-center gap-3 p-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-danger text-danger">
                                <x-heroicon-o-arrow-left-start-on-rectangle class="h-5 w-5" />
                            </span>
                            <span class="flex-1 text-xs font-semibold text-danger">Logout</span>
                            <x-heroicon-o-chevron-right class="h-4 w-4 text-muted" />
                        </x-card>
                    </button>
                </form>
            </div>
        </section>
    </div>
</x-layouts.tenant>
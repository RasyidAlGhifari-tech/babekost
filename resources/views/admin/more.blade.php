<x-layouts.admin title="More">
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
                <x-badge>Admin</x-badge>
            </div>
        </x-card>

        @php
            $groups = [
                'Operasional' => [
                    ['Bookings',  'clipboard-document-list', 'admin.bookings',   $pendingBookings, 'bg-tint-warning text-warning'],
                    ['Complains', 'chat-bubble-left-right',  'admin.complaints', $newComplaints,   'bg-danger text-white'],
                ],
                'Data' => [
                    ['Facilities',          'cube',         'admin.facilities', 0, ''],
                    ['Gallery and Buildings','photo',       'admin.gallery',    0, ''],
                    ['Promo and Events',    'megaphone',    'admin.promos',     0, ''],
                    ['Tenant Accounts',     'users',        'admin.accounts',   0, ''],
                ],
                'Akun' => [
                    ['Ubah password', 'lock-closed', 'admin.password', 0, ''],
                ],
            ];
        @endphp

        @foreach ($groups as $title => $items)
            <section class="space-y-2">
                <h3 class="text-xs font-semibold text-slate">{{ $title }}</h3>
                @foreach ($items as [$label, $icon, $route, $count, $tone])
                    <a href="{{ route($route) }}" class="block">
                        <x-card class="flex items-center gap-3 p-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                                <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-5 w-5" />
                            </span>
                            <span class="flex-1 text-xs font-semibold">{{ $label }}</span>
                            @if ($count)
                                <span class="flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-bold {{ $tone }}">{{ $count }}</span>
                            @endif
                            <x-heroicon-o-chevron-right class="h-4 w-4 text-muted" />
                        </x-card>
                    </a>
                @endforeach
            </section>
        @endforeach

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left">
                <x-card class="flex items-center gap-3 p-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-tint-danger text-danger">
                        <x-heroicon-o-arrow-left-start-on-rectangle class="h-5 w-5" />
                    </span>
                    <span class="flex-1 text-xs font-semibold text-danger">Logout</span>
                </x-card>
            </button>
        </form>
    </div>
</x-layouts.admin>
@props(['title' => 'Admin'])
<x-mobile-shell :title="$title">
    <x-app-bar :title="$title" :logo="$title === 'Dashboard'">
        <x-slot:action>
            @isset($action)
                {{ $action }}
            @else
                <span class="relative flex h-10 w-10 items-center justify-center rounded-full bg-tint-gold text-gold-700">
                    <x-heroicon-o-bell class="h-5 w-5" />
                    <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-danger"></span>
                </span>
            @endisset
        </x-slot:action>
    </x-app-bar>

    {{ $slot }}

    <x-slot:nav>
        <x-bottom-nav :items="[
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2', 'match' => ['admin.dashboard']],
            ['label' => 'Rooms',     'route' => 'admin.rooms',     'icon' => 'icon-bed'],
            ['label' => 'Finance',   'route' => 'admin.finance',   'icon' => 'banknotes'],
            ['label' => 'More', 'route' => 'admin.more', 'icon' => 'bars-3', 'match' => ['admin.more', 'admin.bookings*', 'admin.complaints*', 'admin.facilities*', 'admin.gallery*', 'admin.promos*', 'admin.accounts*', 'admin.password*']],
            ]" />
    </x-slot:nav>
</x-mobile-shell>
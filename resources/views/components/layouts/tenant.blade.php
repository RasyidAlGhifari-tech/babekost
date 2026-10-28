@props(['title' => 'Babekost', 'greeting' => null])
<x-mobile-shell :title="$title">
    <x-app-bar :title="$greeting ?? $title" :logo="(bool) $greeting">
        <x-slot:action>
            @isset($action)
                {{ $action }}
            @else
                <x-notification-bell />
            @endisset
        </x-slot:action>
    </x-app-bar>

    {{ $slot }}

    <x-slot:nav>
        <x-bottom-nav :items="[
            ['label' => 'Home',       'route' => 'tenant.home',       'icon' => 'home', 'match' => ['tenant.home']],
            ['label' => 'Booking',    'route' => 'tenant.booking',    'icon' => 'calendar'],
            ['label' => 'Payment',    'route' => 'tenant.payment',    'icon' => 'receipt-percent'],
            ['label' => 'Complaints', 'route' => 'tenant.complaints', 'icon' => 'chat-bubble-left-right'],
            ['label' => 'Profile',    'route' => 'tenant.profile',    'icon' => 'user'],
        ]" />
    </x-slot:nav>
</x-mobile-shell>
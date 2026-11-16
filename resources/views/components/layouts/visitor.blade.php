@props(['title' => config('babekost.name'), 'logo' => false])
<x-mobile-shell :title="$title">
    <x-app-bar :title="$title" :logo="$logo">
        <x-slot:action>
            @isset($action)
                {{ $action }}
            @else
                <a href="{{ config('babekost.whatsapp.url') }}" target="_blank" rel="noopener"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-tint-success text-success" aria-label="WhatsApp">
                    <x-icon-whatsapp class="h-5 w-5" />
                </a>
            @endisset
        </x-slot:action>
    </x-app-bar>

    {{ $slot }}

    <x-slot:nav>
        <x-bottom-nav :items="[
            ['label' => 'Home',    'route' => 'home',            'icon' => 'home'],
            ['label' => 'Rooms',   'route' => 'visitor.rooms',   'icon' => 'icon-bed'],
            ['label' => 'Gallery', 'route' => 'visitor.gallery', 'icon' => 'photo'],
            ['label' => 'News',   'route' => 'visitor.news',  'icon' => 'megaphone'],
            ['label' => 'Login', 'route' => 'login', 'icon' => 'arrow-right-end-on-rectangle', 'match' => ['login', 'register']],
        ]" />
    </x-slot:nav>
</x-mobile-shell>
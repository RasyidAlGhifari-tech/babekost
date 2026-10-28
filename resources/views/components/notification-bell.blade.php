@php $count = auth()->user()->unreadNotifications()->count(); @endphp
<a href="{{ route('notifications') }}"
    class="relative flex h-10 w-10 items-center justify-center rounded-full bg-tint-gold text-gold-700">
    <x-heroicon-o-bell class="h-5 w-5" />
    @if ($count)
        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger px-1 text-[10px] font-bold text-white">
            {{ $count > 9 ? '9+' : $count }}
        </span>
    @endif
</a>
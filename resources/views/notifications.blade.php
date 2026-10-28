<x-dynamic-component :component="auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.tenant'" title="Notifikasi">
    <div class="flex flex-col gap-3 p-4">

        @if ($notifications->total() > 0 && auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}" class="text-right">
                @csrf
                <button class="text-xs font-medium text-gold-700">Tandai semua dibaca</button>
            </form>
        @endif

        @forelse ($notifications as $n)
            <a href="{{ route('notifications.open', $n->id) }}" class="block">
                <x-card class="flex items-start gap-3 p-3 {{ $n->read_at ? 'opacity-70' : '' }}">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-tint-gold text-gold-700">
                        <x-dynamic-component :component="'heroicon-o-' . ($n->data['icon'] ?? 'bell')" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold">{{ $n->data['title'] }}</p>
                        <p class="mt-0.5 text-[11px] text-slate">{{ $n->data['body'] }}</p>
                        <p class="mt-1 text-[10px] text-muted">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($n->read_at)
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-danger"></span>
                    @endunless
                </x-card>
            </a>
        @empty
            <p class="py-10 text-center text-sm text-slate">Belum ada notifikasi.</p>
        @endforelse

        {{ $notifications->links() }}
    </div>
</x-dynamic-component>
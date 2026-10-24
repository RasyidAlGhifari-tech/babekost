<x-layouts.admin title="Complaints">
    <div class="flex flex-col gap-3 p-4">
        <x-back />

        @if (session('status'))
            <div class="rounded-lg bg-tint-success p-3 text-xs text-success">{{ session('status') }}</div>
        @endif

        <div class="flex gap-2 text-xs font-medium">
            @foreach (['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $key => $label)
                <a href="{{ route('admin.complaints', ['tab' => $key]) }}"
                   class="rounded-full border px-3 py-1 {{ $tab === $key ? 'border-gold-border bg-tint-gold text-gold-700' : 'border-line-chip bg-white text-slate' }}">
                    {{ $label }} {{ $counts[$key] ?? 0 }}
                </a>
            @endforeach
        </div>

        @forelse ($complaints as $c)
            <x-card class="space-y-2 p-3">
                <div class="flex items-start justify-between">
                    <p class="text-xs font-semibold text-gold-700">
                        {{ $c->room ? 'Kamar ' . $c->room->number : '-' }} • {{ $c->tenant->user->name }}
                    </p>
                    <x-badge :status="['baru' => 'baru', 'diproses' => 'menunggu', 'selesai' => 'aktif'][$c->status]">{{ ucfirst($c->status) }}</x-badge>
                </div>
                <p class="text-xs">{{ $c->description }}</p>
                <p class="text-[11px] text-slate">Diajukan {{ $c->created_at->translatedFormat('j M Y') }}</p>

                @if ($c->status !== 'selesai')
                    <form method="POST" action="{{ route('admin.complaints.update', $c) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $c->status === 'baru' ? 'diproses' : 'selesai' }}">
                        <x-button type="submit" :variant="$c->status === 'baru' ? 'outline' : 'primary'">
                            {{ $c->status === 'baru' ? 'Tandai diproses' : 'Tandai selesai' }}
                        </x-button>
                    </form>
                @endif
            </x-card>
        @empty
            <p class="py-10 text-center text-sm text-slate">Tidak ada keluhan.</p>
        @endforelse
    </div>
</x-layouts.admin>
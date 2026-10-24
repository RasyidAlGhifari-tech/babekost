@props(['href' => null])
<a href="{{ $href ?? route('admin.more') }}" class="flex items-center gap-1 text-xs font-medium text-gold-700">
    <x-heroicon-o-arrow-left class="h-4 w-4" /> Kembali
</a>
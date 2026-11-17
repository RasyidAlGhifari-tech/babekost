@props(['url' => null, 'alt' => '', 'icon' => 'h-6 w-6'])

@if ($url)
    <img src="{{ $url }}" alt="{{ $alt }}" loading="lazy" draggable="false"
        {{ $attributes->merge(['class' => 'object-cover']) }}>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center']) }} style="background: var(--grad-photo)">
        <x-heroicon-o-photo class="{{ $icon }} text-gold-500" />
    </div>
@endif
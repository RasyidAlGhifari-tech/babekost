@props(['tabs', 'active'])
<div class="flex rounded-lg bg-segmented p-1 text-xs font-medium">
    @foreach ($tabs as $key => $label)
        <a href="{{ url()->current() }}?tab={{ $key }}"
            class="flex-1 rounded-md py-2 text-center {{ $active === $key ? 'bg-white text-ink shadow-segment' : 'text-slate' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
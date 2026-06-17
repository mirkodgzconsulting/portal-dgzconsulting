@props([
    'item' => null,
    'lazy' => true,
    'constrained' => true,
])

<img
    src="{{ $src }}"
    alt="{{ $alt ?? '' }}"
    @if ($lazy) loading="lazy" @endif
    style="min-height:60px;"
    @class([
       'h-full w-full object-cover',
    ])
    {{ $attributes->merge() }}
/>

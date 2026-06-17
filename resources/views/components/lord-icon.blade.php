@props([
    'icon',
    'size' => 48,
    'trigger' => 'hover',
    'stroke' => 'light',
    'primary' => '#121331',
    'secondary' => '#0F65E6',
])

<lord-icon
    src="/icons/{{ $icon }}.json"
    trigger="{{ $trigger }}"
    stroke="{{ $stroke }}"
    colors="primary:{{ $primary }},secondary:{{ $secondary }}"
    style="width:{{ $size }}px;height:{{ $size }}px"
    {{ $attributes }}
></lord-icon>

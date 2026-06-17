@php
    $record = $getRecord();
    $isSvg = curator()->isSvg($record->ext);
@endphp

<div {{ $attributes->merge($getExtraAttributes())->class(['curator-grid-column absolute inset-0 rounded-t-xl overflow-hidden']) }}>
    <div @class([
        'rounded-t-xl h-full overflow-hidden bg-gray-100 dark:bg-gray-950/50',
        'checkered' => $isSvg,
    ])>
        <img
            src="{{ $record->mediumUrl }}"
            alt="{{ $record->alt ?? $record->pretty_name }}"
            loading="lazy"
            class="w-full h-full object-cover"
            x-on:click="toggleSelectedRecord('{{ $record->id }}')"
        />
        <x-curator::display.info-overlay :label="$record->pretty_name" :size="$record->size" />
    </div>
</div>

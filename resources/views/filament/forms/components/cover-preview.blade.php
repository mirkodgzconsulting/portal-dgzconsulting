@php
    $url = $get('cover_image');
    $spatieUrl = $getRecord()?->getFirstMediaUrl('cover');
    $displayUrl = $url ?: $spatieUrl;
@endphp

<div
    x-data="{ previewUrl: @js($displayUrl ?? '') }"
    x-init="
        $watch('$wire.data.cover_image', value => {
            if (value) previewUrl = value;
            else previewUrl = '';
        });
    "
    class="w-full"
>
    <template x-if="previewUrl">
        <div style="position: relative; display: inline-block;">
            <img
                :src="previewUrl"
                alt="Cover preview"
                style="max-height: 180px; object-fit: contain; border-radius: 8px; border: 1px solid #e4e4e7;"
            />
            <button
                type="button"
                @click="previewUrl = ''; $wire.set('data.cover_image', '')"
                style="position: absolute; top: 6px; right: 6px; width: 24px; height: 24px; background: rgba(0,0,0,0.6); border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                title="Quitar imagen"
            >
                <svg style="width: 14px; height: 14px; color: white;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>

<div
    x-data="{
        tab: 'library',
        media: [],
        search: '',
        selected: null,
        loading: true,
        async fetchMedia() {
            this.loading = true;
            const res = await fetch('/api/client-media?search=' + encodeURIComponent(this.search));
            const json = await res.json();
            this.media = json.data;
            this.loading = false;
        },
        select(item) {
            this.selected = this.selected?.id === item.id ? null : item;
        },
        confirm() {
            if (this.selected) {
                $wire.set('data.cover_image', this.selected.url);
                $dispatch('close-modal', { id: 'browse_media' });
            }
        }
    }"
    x-init="fetchMedia()"
    class="space-y-4"
>
    {{-- Tabs --}}
    <div class="flex border-b border-zinc-200">
        <button
            @click="tab = 'library'"
            :class="tab === 'library' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700'"
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        >
            Media Library
        </button>
        <button
            @click="tab = 'upload'"
            :class="tab === 'upload' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700'"
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        >
            Upload files
        </button>
    </div>

    {{-- Library Tab --}}
    <div x-show="tab === 'library'" class="space-y-3">
        <input
            x-model.debounce.300ms="search"
            @input.debounce.300ms="fetchMedia()"
            type="text"
            placeholder="Search media..."
            class="w-full h-9 px-3 rounded-md border border-zinc-200 bg-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        />

        <div x-show="loading" class="text-center py-12">
            <div class="w-6 h-6 mx-auto border-2 border-zinc-300 border-t-blue-600 rounded-full animate-spin"></div>
        </div>

        <div x-show="!loading && media.length === 0" class="text-center py-12 text-zinc-400">
            <p class="text-sm">No hay imágenes. Sube una desde "Upload files".</p>
        </div>

        <template x-if="!loading && media.length > 0">
        <div style="display:grid; grid-template-columns:repeat(6, 1fr); gap:8px; max-height:400px; overflow-y:auto; padding:4px;">
            <template x-for="item in media" :key="item.id">
                <div
                    @click="select(item)"
                    style="cursor:pointer; position:relative; aspect-ratio:1; border-radius:8px; overflow:hidden; border:2px solid transparent;"
                    :style="selected?.id === item.id ? 'border-color:#3b82f6; box-shadow:0 0 0 2px #bfdbfe;' : ''"
                    @mouseenter="$el.style.borderColor = selected?.id === item.id ? '#3b82f6' : '#d4d4d8'"
                    @mouseleave="$el.style.borderColor = selected?.id === item.id ? '#3b82f6' : 'transparent'"
                >
                    <img :src="item.url" :alt="item.name" style="width:100%; height:100%; object-fit:cover;" loading="lazy" />
                    <div
                        x-show="selected?.id === item.id"
                        style="position:absolute; top:4px; right:4px; width:20px; height:20px; background:#3b82f6; border-radius:50%; display:flex; align-items:center; justify-content:center;"
                    >
                        <svg style="width:12px; height:12px; color:white;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </template>
        </div>
        </template>

        {{-- Selected info bar --}}
        <div x-show="selected" style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:#f4f4f5; border-radius:8px; margin-top:8px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <img :src="selected?.url" style="width:48px; height:48px; object-fit:cover; border-radius:6px;" />
                <div>
                    <div style="font-size:14px; font-weight:500; color:#18181b;" x-text="selected?.name"></div>
                    <div style="font-size:12px; color:#71717a;" x-text="selected?.size"></div>
                </div>
            </div>
            <button
                @click="confirm()"
                style="height:36px; padding:0 16px; background:#2563eb; color:white; font-size:14px; font-weight:500; border-radius:6px; border:none; cursor:pointer;"
            >
                Set featured image
            </button>
        </div>
    </div>

    {{-- Upload Tab --}}
    <div x-show="tab === 'upload'" class="py-4">
        <p class="text-sm text-zinc-500 text-center">Usa el campo "Subir nueva imagen" del formulario para subir archivos directamente.</p>
    </div>
</div>

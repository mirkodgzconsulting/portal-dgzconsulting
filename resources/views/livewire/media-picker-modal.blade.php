<div class="space-y-4">
    {{-- Tabs --}}
    <div class="flex border-b border-zinc-200 dark:border-zinc-700">
        <button
            wire:click="$set('activeTab', 'library')"
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'library' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}"
        >
            Media Library
        </button>
        <button
            wire:click="$set('activeTab', 'upload')"
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'upload' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}"
        >
            Upload files
        </button>
    </div>

    @if($activeTab === 'library')
        {{-- Search --}}
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search media..."
            class="w-full h-9 px-3 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm"
        />

        {{-- Grid --}}
        @if($media->isEmpty())
            <div class="text-center py-12 text-zinc-400">
                <p class="text-sm">No hay imágenes. Sube una desde la pestaña "Upload files".</p>
            </div>
        @else
            <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2 max-h-[400px] overflow-y-auto p-1">
                @foreach($media as $item)
                    <div
                        wire:click="selectMedia({{ $item->id }})"
                        class="cursor-pointer relative aspect-square rounded-lg overflow-hidden border-2 transition-all
                            {{ $selectedId === $item->id ? 'border-blue-500 ring-2 ring-blue-200' : 'border-transparent hover:border-zinc-300' }}"
                    >
                        <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}" class="w-full h-full object-cover" loading="lazy" />
                        @if($selectedId === $item->id)
                            <div class="absolute top-1 right-1 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Selected info + confirm --}}
            @if($selectedId)
                @php $selected = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($selectedId); @endphp
                @if($selected)
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <img src="{{ $selected->getUrl() }}" class="w-12 h-12 object-cover rounded" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $selected->file_name }}</div>
                                <div class="text-xs text-zinc-500">{{ $selected->human_readable_size }}</div>
                            </div>
                        </div>
                        <button
                            wire:click="confirmSelection"
                            class="h-9 px-4 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-500 transition-colors"
                        >
                            Set featured image
                        </button>
                    </div>
                @endif
            @endif
        @endif

    @else
        {{-- Upload tab --}}
        <div class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
            <input type="file" wire:model="uploadFile" accept="image/*" class="hidden" id="picker-upload" />
            <label for="picker-upload" class="cursor-pointer">
                <svg class="w-12 h-12 mx-auto mb-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M12 16V4m0 0l-4 4m4-4l4 4M4 20h16"/></svg>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Drop file here or <span class="text-blue-600">Browse</span></p>
                <p class="text-xs text-zinc-400 mt-1">JPG, PNG, WebP, GIF — max 10 MB</p>
            </label>
            <div wire:loading wire:target="uploadFile" class="mt-4">
                <div class="w-6 h-6 mx-auto border-2 border-zinc-300 border-t-blue-600 rounded-full animate-spin"></div>
                <p class="text-xs text-zinc-500 mt-2">Uploading to cloud...</p>
            </div>
        </div>
    @endif
</div>

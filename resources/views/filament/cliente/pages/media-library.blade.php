<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Upload Area --}}
        <div class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
            <input
                type="file"
                wire:model="newFiles"
                multiple
                accept="image/*"
                class="hidden"
                id="media-upload"
            />
            <label for="media-upload" class="cursor-pointer">
                <svg class="w-10 h-10 mx-auto mb-2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M12 16V4m0 0l-4 4m4-4l4 4M4 20h16"/></svg>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Drop files here or <span class="text-blue-600">Browse</span></p>
                <p class="text-xs text-zinc-400 mt-1">JPG, PNG, WebP, GIF — max 10 MB</p>
            </label>
            <div wire:loading wire:target="newFiles" class="mt-3">
                <div class="w-6 h-6 mx-auto border-2 border-zinc-300 border-t-blue-600 rounded-full animate-spin"></div>
                <p class="text-xs text-zinc-500 mt-1">Uploading...</p>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <button wire:click="toggleView" class="p-2 rounded-md border {{ $viewMode === 'grid' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </button>
                <button wire:click="toggleView" class="p-2 rounded-md border {{ $viewMode === 'list' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>

            <div class="flex-1 max-w-sm">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search media..."
                    class="w-full h-10 px-4 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                />
            </div>
        </div>

        <div class="flex gap-6">
            {{-- Media Grid/List --}}
            <div class="flex-1">
                @php $media = $this->getMediaProperty(); @endphp

                @if($media->isEmpty())
                    <div class="text-center py-20 text-zinc-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        <p class="text-sm">No hay imágenes todavía.</p>
                        <p class="text-xs text-zinc-400 mt-1">Sube imágenes desde tus posts o portfolio.</p>
                    </div>
                @else
                    @if($viewMode === 'grid')
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                            @foreach($media as $item)
                                <div
                                    wire:click="selectMedia({{ $item->id }})"
                                    class="cursor-pointer group relative aspect-square rounded-lg overflow-hidden border-2 transition-all duration-150
                                        {{ $selectedMediaId === $item->id ? 'border-blue-500 ring-2 ring-blue-200' : 'border-transparent hover:border-zinc-300' }}"
                                >
                                    <img
                                        src="{{ $item->getUrl() }}"
                                        alt="{{ $item->name }}"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                    @if($selectedMediaId === $item->id)
                                        <div class="absolute top-2 right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                            @foreach($media as $item)
                                <div
                                    wire:click="selectMedia({{ $item->id }})"
                                    class="flex items-center gap-4 px-4 py-3 cursor-pointer border-b border-zinc-100 dark:border-zinc-800 last:border-b-0 transition-colors
                                        {{ $selectedMediaId === $item->id ? 'bg-blue-50 dark:bg-blue-950' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800' }}"
                                >
                                    <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}" class="w-12 h-12 object-cover rounded" loading="lazy" />
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white truncate">{{ $item->file_name }}</div>
                                        <div class="text-xs text-zinc-500">{{ $item->human_readable_size }} · {{ $item->created_at->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4">
                        {{ $media->links() }}
                    </div>
                @endif
            </div>

            {{-- Sidebar Detail Panel --}}
            @if($selectedMediaId && ($selected = $this->getSelectedMedia()))
                <div class="w-72 shrink-0 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4 self-start sticky top-4">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Attachment Details</div>

                    <img src="{{ $selected->getUrl() }}" alt="{{ $selected->name }}" class="w-full rounded-lg" />

                    <div class="space-y-2 text-xs text-zinc-500">
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">Nombre:</span> {{ $selected->file_name }}</div>
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">Tipo:</span> {{ $selected->mime_type }}</div>
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">Tamaño:</span> {{ $selected->human_readable_size }}</div>
                        <div><span class="font-medium text-zinc-700 dark:text-zinc-300">Subida:</span> {{ $selected->created_at->format('d M Y, H:i') }}</div>
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">URL:</span>
                            <input type="text" value="{{ $selected->getUrl() }}" readonly
                                class="w-full mt-1 px-2 py-1 text-xs border border-zinc-200 dark:border-zinc-700 rounded bg-zinc-50 dark:bg-zinc-800"
                                onclick="this.select(); navigator.clipboard.writeText(this.value);"
                            />
                        </div>
                    </div>

                    <button
                        wire:click="deleteMedia"
                        wire:confirm="¿Eliminar esta imagen permanentemente?"
                        class="w-full h-9 text-sm font-medium text-red-600 border border-red-200 rounded-md hover:bg-red-50 transition-colors"
                    >
                        Eliminar
                    </button>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>

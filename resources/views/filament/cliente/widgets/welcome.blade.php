<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <lord-icon
                    src="https://cdn.lordicon.com/rpviwvwn.json"
                    trigger="hover"
                    stroke="light"
                    state="hover-rotate-up-to-down"
                    colors="primary:#0070f3,secondary:#71717a"
                    style="width:48px;height:48px">
                </lord-icon>
                <div>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                        ¡Hola, {{ $userName }}!
                    </h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        @if($isEditor)
                            Panel de edición — gestiona los artículos del blog.
                        @else
                            Bienvenido a tu panel de gestión.
                        @endif
                    </p>
                    <div class="mt-2" style="font-family: 'Urbanist', sans-serif; line-height: 1.1;">
                        <div class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">DGZ</div>
                        <div class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">Consulting.</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="animate-fade-in-up border border-zinc-200 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-900 p-5" style="animation-delay: 0ms">
                    <div class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $totalPosts }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Posts totales</div>
                </div>
                <div class="animate-fade-in-up border border-zinc-200 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-900 p-5" style="animation-delay: 50ms">
                    <div class="text-2xl font-semibold text-emerald-600">{{ $publishedPosts }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Publicados</div>
                </div>
                <div class="animate-fade-in-up border border-zinc-200 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-900 p-5" style="animation-delay: 100ms">
                    <div class="text-2xl font-semibold text-amber-500">{{ $draftPosts }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Borradores</div>
                </div>
                @unless($isEditor)
                <div class="animate-fade-in-up border border-zinc-200 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-900 p-5" style="animation-delay: 150ms">
                    <div class="text-2xl font-semibold text-zinc-700 dark:text-zinc-200">{{ $sitesCount }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Sitios web</div>
                </div>
                @endunless
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('filament.cliente.resources.posts.create') }}"
                   class="inline-flex items-center gap-1.5 h-10 rounded-md bg-zinc-900 dark:bg-white px-4 text-sm font-medium text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-200 transition-colors duration-200">
                    + Nuevo Post
                </a>
                <a href="{{ route('filament.cliente.resources.posts.index') }}"
                   class="inline-flex items-center gap-1.5 h-10 rounded-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 px-4 text-sm font-medium text-zinc-600 dark:text-zinc-300 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors duration-200">
                    Ver todos los posts
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    ¡Hola, {{ $userName }}!
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    @if($isEditor)
                        Panel de edición — gestiona los artículos del blog.
                    @else
                        Bienvenido a tu panel de gestión.
                    @endif
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <div class="text-3xl font-bold text-primary-600">{{ $totalPosts }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Posts totales</div>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <div class="text-3xl font-bold text-green-600">{{ $publishedPosts }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Publicados</div>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <div class="text-3xl font-bold text-amber-500">{{ $draftPosts }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Borradores</div>
                </div>
                @unless($isEditor)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <div class="text-3xl font-bold text-gray-700 dark:text-gray-200">{{ $sitesCount }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sitios web</div>
                </div>
                @endunless
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('filament.cliente.resources.posts.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-500 transition">
                    + Nuevo Post
                </a>
                <a href="{{ route('filament.cliente.resources.posts.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Ver todos los posts
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

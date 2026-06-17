<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista previa — {{ $post->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .prose img { max-width: 100%; height: auto; border-radius: 0.5rem; }
        .prose h1, .prose h2, .prose h3 { margin-top: 1.5em; margin-bottom: 0.5em; }
        .prose p { margin-bottom: 1em; line-height: 1.75; }
        .prose ul, .prose ol { margin-left: 1.5em; margin-bottom: 1em; }
        .prose blockquote { border-left: 4px solid #0F65E6; padding-left: 1em; color: #4b5563; font-style: italic; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    {{-- Preview banner --}}
    <div class="bg-amber-500 text-white text-center py-2 text-sm font-medium sticky top-0 z-50">
        VISTA PREVIA — Este post {{ $post->published ? 'está publicado' : 'es un borrador' }}
    </div>

    <article class="max-w-3xl mx-auto px-6 py-12">
        {{-- Category --}}
        @if($post->category)
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 mb-4">
                {{ $post->category->name }}
            </span>
        @endif

        {{-- Title --}}
        <h1 class="text-4xl font-bold text-gray-900 leading-tight mb-4">
            {{ $post->title }}
        </h1>

        {{-- Meta --}}
        <div class="flex items-center gap-4 text-sm text-gray-500 mb-8">
            @if($post->author)
                <span>{{ $post->author }}</span>
                <span>·</span>
            @endif
            @if($post->pub_date)
                <time>{{ $post->pub_date->format('d M Y') }}</time>
                <span>·</span>
            @endif
            @php
                $wordCount = str_word_count(strip_tags($post->content ?? ''));
                $readTime = max(1, (int) ceil($wordCount / 200));
            @endphp
            <span>{{ $readTime }} min de lectura</span>
        </div>

        {{-- Description --}}
        @if($post->description)
            <p class="text-lg text-gray-600 italic border-l-4 border-blue-500 pl-4 mb-8">
                {{ $post->description }}
            </p>
        @endif

        {{-- Cover image --}}
        @if($post->cover_image)
            <img src="{{ $post->cover_image }}"
                 alt="{{ $post->title }}"
                 class="w-full rounded-xl mb-10 shadow-sm">
        @endif

        {{-- Content --}}
        <div class="prose prose-lg max-w-none text-gray-800">
            {!! $post->content !!}
        </div>

        {{-- Tags --}}
        @if($post->tags && count($post->tags) > 0)
            <div class="mt-10 pt-6 border-t border-gray-200 flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                    <span class="px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                        #{{ $tag }}
                    </span>
                @endforeach
            </div>
        @endif
    </article>
</body>
</html>

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class SitePostsController extends Controller
{
    public function index(Site $site): JsonResponse
    {
        $posts = $site->posts()
            ->with('category')
            ->where('published', true)
            ->orderByDesc('pub_date')
            ->get()
            ->map(fn ($post) => [
                'category'        => $post->category?->name,
                'category_slug'   => $post->category?->slug,
                'slug'            => $post->slug,
                'title'           => $post->title,
                'description'     => $post->description,
                'content'         => $post->content,
                'tags'            => $post->tags ?? [],
                'author'          => $post->author,
                'pubDate'         => $post->pub_date->toDateString(),
                'cover_image'     => $post->cover_image_url,
                'featured'        => $post->featured,
                'seo_title'       => $post->seo_title,
                'seo_description' => $post->seo_description,
                'focus_keyword'   => $post->focus_keyword,
                'canonical_url'   => $post->canonical_url,
                'og_title'        => $post->og_title,
                'og_description'  => $post->og_description,
                'og_image'        => $post->og_image,
                'robots'          => $post->robots ?? 'index,follow',
            ]);

        return response()->json(['data' => $posts]);
    }
}

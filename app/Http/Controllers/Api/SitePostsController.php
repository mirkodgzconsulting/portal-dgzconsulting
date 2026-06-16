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
            ->where('published', true)
            ->orderByDesc('pub_date')
            ->get()
            ->map(fn ($post) => [
                'slug' => $post->slug,
                'title' => $post->title,
                'description' => $post->description,
                'content' => $post->content,
                'tags' => $post->tags ?? [],
                'author' => $post->author,
                'pubDate' => $post->pub_date->toDateString(),
                'cover_image' => $post->cover_image_url,
                'featured' => $post->featured,
            ]);

        return response()->json(['data' => $posts]);
    }
}

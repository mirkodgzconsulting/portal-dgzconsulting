<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class SiteCategoriesController extends Controller
{
    public function index(Site $site): JsonResponse
    {
        $categories = $site->categories()
            ->withCount('posts')
            ->orderBy('name')
            ->get()
            ->map(fn ($cat) => [
                'name'       => $cat->name,
                'slug'       => $cat->slug,
                'post_count' => $cat->posts_count,
            ]);

        return response()->json(['data' => $categories]);
    }
}

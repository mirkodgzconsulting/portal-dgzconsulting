<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class SitePortfolioController extends Controller
{
    public function index(Site $site): JsonResponse
    {
        $categories = $site->portfolioCategories()
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($cat) => [
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'cover_image' => $cat->cover_image,
                'items' => $cat->items->map(fn ($item) => [
                    'title' => $item->title,
                    'description' => $item->description,
                    'image_url' => $item->image_url,
                    'sort_order' => $item->sort_order,
                ]),
            ]);

        return response()->json(['data' => $categories]);
    }
}

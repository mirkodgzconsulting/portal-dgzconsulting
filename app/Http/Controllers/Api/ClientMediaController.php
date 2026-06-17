<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Post;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ClientMediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        if (! $clientId) {
            return response()->json(['data' => []]);
        }

        $siteIds = Site::where('client_id', $clientId)->pluck('id');
        $postIds = Post::whereIn('site_id', $siteIds)->pluck('id');
        $portfolioCatIds = PortfolioCategory::whereIn('site_id', $siteIds)->pluck('id');
        $portfolioItemIds = PortfolioItem::whereIn('portfolio_category_id', $portfolioCatIds)->pluck('id');

        $media = Media::query()
            ->where(function ($query) use ($clientId, $postIds, $portfolioCatIds, $portfolioItemIds) {
                $query->where(function ($q) use ($clientId) {
                    $q->where('model_type', Client::class)->where('model_id', $clientId);
                })->orWhere(function ($q) use ($postIds) {
                    $q->where('model_type', Post::class)->whereIn('model_id', $postIds);
                })->orWhere(function ($q) use ($portfolioCatIds) {
                    $q->where('model_type', PortfolioCategory::class)->whereIn('model_id', $portfolioCatIds);
                })->orWhere(function ($q) use ($portfolioItemIds) {
                    $q->where('model_type', PortfolioItem::class)->whereIn('model_id', $portfolioItemIds);
                });
            })
            ->when($request->search, fn ($q, $s) => $q->where('file_name', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->limit(60)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'name' => $m->file_name,
                'size' => $m->human_readable_size,
                'date' => $m->created_at->format('d/m/Y'),
            ]);

        return response()->json(['data' => $media]);
    }
}

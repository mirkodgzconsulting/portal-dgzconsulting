<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\Post;
use App\Models\Site;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeWidget extends Widget
{
    protected string $view = 'filament.cliente.widgets.welcome';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        $isEditor = Auth::guard('client_user')->check();
        $userName = Auth::guard('client')->user()?->name
            ?? Auth::guard('client_user')->user()?->name
            ?? 'Usuario';

        $siteIds = Site::where('client_id', $clientId)->pluck('id');

        $totalPosts = Post::whereIn('site_id', $siteIds)->count();
        $publishedPosts = Post::whereIn('site_id', $siteIds)->where('published', true)->count();
        $draftPosts = $totalPosts - $publishedPosts;
        $sitesCount = $siteIds->count();

        return [
            'userName' => $userName,
            'isEditor' => $isEditor,
            'totalPosts' => $totalPosts,
            'publishedPosts' => $publishedPosts,
            'draftPosts' => $draftPosts,
            'sitesCount' => $sitesCount,
        ];
    }
}

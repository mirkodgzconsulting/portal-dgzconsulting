<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\Site;
use App\Models\Subscription;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SubscriptionsWidget extends Widget
{
    protected string $view = 'filament.cliente.widgets.subscriptions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::guard('client')->check();
    }

    protected function getViewData(): array
    {
        $clientId = Auth::guard('client')->id();
        $siteIds = Site::where('client_id', $clientId)->pluck('id');

        $subscriptions = Subscription::whereIn('site_id', $siteIds)
            ->with('site')
            ->orderBy('renewal_date')
            ->get();

        return [
            'subscriptions' => $subscriptions,
        ];
    }
}

<?php

namespace App\Filament\Cliente\Resources\Subscriptions\Pages;

use App\Filament\Cliente\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

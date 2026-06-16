<?php

namespace App\Filament\Cliente\Resources\Sites\Pages;

use App\Filament\Cliente\Resources\Sites\SiteResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSite extends ViewRecord
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

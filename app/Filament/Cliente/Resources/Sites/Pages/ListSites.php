<?php

namespace App\Filament\Cliente\Resources\Sites\Pages;

use App\Filament\Cliente\Resources\Sites\SiteResource;
use Filament\Resources\Pages\ListRecords;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

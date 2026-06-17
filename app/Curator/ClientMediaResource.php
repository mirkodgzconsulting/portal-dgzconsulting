<?php

namespace App\Curator;

use Awcodes\Curator\Resources\Media\MediaResource;
use Filament\Tables\Table;

class ClientMediaResource extends MediaResource
{
    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }
}

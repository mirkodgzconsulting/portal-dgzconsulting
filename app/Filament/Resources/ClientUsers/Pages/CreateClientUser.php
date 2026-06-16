<?php

namespace App\Filament\Resources\ClientUsers\Pages;

use App\Filament\Resources\ClientUsers\ClientUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientUser extends CreateRecord
{
    protected static string $resource = ClientUserResource::class;
}

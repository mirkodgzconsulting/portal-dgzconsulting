<?php

namespace App\Filament\Cliente\Resources\ClientUsers\Pages;

use App\Filament\Cliente\Resources\ClientUsers\ClientUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientUser extends EditRecord
{
    protected static string $resource = ClientUserResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

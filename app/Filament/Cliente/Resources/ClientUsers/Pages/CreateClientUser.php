<?php

namespace App\Filament\Cliente\Resources\ClientUsers\Pages;

use App\Filament\Cliente\Resources\ClientUsers\ClientUserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateClientUser extends CreateRecord
{
    protected static string $resource = ClientUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['client_id'] = Auth::guard('client')->id();
        return $data;
    }
}

<?php

namespace App\Curator;

use Awcodes\Curator\Resources\Media\MediaResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ClientMediaResource extends MediaResource
{
    protected static ?string $slug = 'media';

    public static function getEloquentQuery(): Builder
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        $query = parent::getEloquentQuery();

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return $query;
    }
}

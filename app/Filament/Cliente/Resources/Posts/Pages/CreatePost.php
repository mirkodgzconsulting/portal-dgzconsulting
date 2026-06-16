<?php

namespace App\Filament\Cliente\Resources\Posts\Pages;

use App\Filament\Cliente\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        abort_unless(
            Auth::guard('client')->user()->sites()->where('has_blog', true)->where('id', $data['site_id'])->exists(),
            403
        );

        return $data;
    }
}

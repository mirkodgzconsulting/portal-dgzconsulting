<?php

namespace App\Filament\Cliente\Resources\Posts\Pages;

use App\Filament\Cliente\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(
            Auth::guard('client')->user()->sites()->where('has_blog', true)->where('id', $data['site_id'])->exists(),
            403
        );

        return $data;
    }
}

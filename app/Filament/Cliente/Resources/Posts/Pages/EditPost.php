<?php

namespace App\Filament\Cliente\Resources\Posts\Pages;

use App\Filament\Cliente\Resources\Posts\PostResource;
use Filament\Actions\Action;
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
            Action::make('preview')
                ->label('Vista previa')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('post.preview', $this->record), shouldOpenInNewTab: true),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        abort_unless(
            \App\Models\Site::where('id', $data['site_id'])
                ->where('client_id', $clientId)
                ->where('has_blog', true)
                ->exists(),
            403
        );

        return $data;
    }
}

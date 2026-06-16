<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    // Prevent deleting yourself
                    if ($this->record->id === auth()->id()) {
                        $action->cancel();
                        $this->notify('danger', 'No puedes eliminar tu propio usuario.');
                    }
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email (login del portal)')
                    ->email()
                    ->required(),
                FileUpload::make('logo')
                    ->image()
                    ->directory('clients')
                    ->avatar(),
            ]);
    }
}

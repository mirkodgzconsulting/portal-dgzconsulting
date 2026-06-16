<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                TextInput::make('secondary_email')
                    ->label('Email secundario')
                    ->email(),
                TextInput::make('phone')
                    ->label('Teléfono / WhatsApp')
                    ->tel(),
                Toggle::make('active')
                    ->label('Cliente activo')
                    ->helperText('Desactiva si ya no tiene servicio con DGZ. No se borra ningún dato, solo se oculta de la lista por defecto.')
                    ->default(true),
                FileUpload::make('logo')
                    ->image()
                    ->directory('clients')
                    ->avatar(),
                Section::make('Acceso al portal')
                    ->description('Deja vacío para no cambiar la contraseña actual.')
                    ->schema([
                        TextInput::make('password')
                            ->label('Nueva contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                            ->placeholder('Escribe para asignar o cambiar la contraseña'),
                    ]),
            ]);
    }
}

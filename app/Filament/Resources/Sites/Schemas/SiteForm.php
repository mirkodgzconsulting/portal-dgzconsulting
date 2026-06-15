<?php

namespace App\Filament\Resources\Sites\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre del sitio')
                    ->required(),
                TextInput::make('domain')
                    ->label('Dominio')
                    ->placeholder('modelooctatrico.com'),
                TextInput::make('admin_url')
                    ->label('URL de administración')
                    ->url()
                    ->placeholder('https://modelooctatrico.com/wp-admin'),
                TextInput::make('cms_type')
                    ->label('Tipo de CMS')
                    ->placeholder('WordPress, Astro, Strapi...'),
                TextInput::make('hosting_provider')
                    ->label('Proveedor de hosting')
                    ->placeholder('SiteGround, Hetzner...'),
                Toggle::make('has_blog')
                    ->label('Tiene módulo de blog')
                    ->helperText('Activa la edición de posts en el portal del cliente'),

                Section::make('Acceso al CMS')
                    ->description('Credenciales guardadas cifradas. Solo visibles en este panel.')
                    ->columns(2)
                    ->components([
                        TextInput::make('cms_username')
                            ->label('Usuario'),
                        TextInput::make('cms_password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable(),
                    ]),
            ]);
    }
}

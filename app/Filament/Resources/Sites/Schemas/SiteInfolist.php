<?php

namespace App\Filament\Resources\Sites\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('client.name')
                    ->label('Cliente'),
                TextEntry::make('name')
                    ->label('Nombre del sitio'),
                TextEntry::make('domain')
                    ->label('Dominio')
                    ->placeholder('-'),
                TextEntry::make('admin_url')
                    ->label('URL de administración')
                    ->placeholder('-'),
                TextEntry::make('cms_type')
                    ->label('Tipo de CMS')
                    ->placeholder('-'),
                TextEntry::make('hosting_provider')
                    ->label('Proveedor de hosting')
                    ->placeholder('-'),
                TextEntry::make('cms_username')
                    ->label('Usuario CMS')
                    ->placeholder('-'),
                IconEntry::make('has_blog')
                    ->label('Tiene blog')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

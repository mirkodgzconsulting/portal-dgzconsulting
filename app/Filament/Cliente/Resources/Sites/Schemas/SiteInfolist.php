<?php

namespace App\Filament\Cliente\Resources\Sites\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
            IconEntry::make('has_blog')
                ->label('Tiene blog')
                ->boolean(),
        ]);
    }
}

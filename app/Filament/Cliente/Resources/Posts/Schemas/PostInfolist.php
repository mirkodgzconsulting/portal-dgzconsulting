<?php

namespace App\Filament\Cliente\Resources\Posts\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('site.name')
                    ->label('Sitio'),
                TextEntry::make('title')
                    ->label('Título'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                ImageEntry::make('cover_image_url')
                    ->label('Imagen de portada')
                    ->placeholder('-'),
                TextEntry::make('tags')
                    ->label('Etiquetas')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('author')
                    ->label('Autor')
                    ->placeholder('-'),
                TextEntry::make('pub_date')
                    ->label('Fecha de publicación')
                    ->date(),
                TextEntry::make('published')
                    ->label('Publicado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextEntry::make('featured')
                    ->label('Destacado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
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

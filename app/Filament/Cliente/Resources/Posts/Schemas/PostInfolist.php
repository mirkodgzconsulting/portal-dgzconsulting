<?php

namespace App\Filament\Cliente\Resources\Posts\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                Section::make()
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label('')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('')
                            ->color('gray')
                            ->columnSpanFull(),
                        ImageEntry::make('cover_image_url')
                            ->label('')
                            ->height(250)
                            ->columnSpanFull()
                            ->extraImgAttributes(['class' => 'rounded-xl w-full object-cover']),
                        TextEntry::make('content')
                            ->label('Contenido')
                            ->html()
                            ->columnSpanFull()
                            ->prose(),
                    ]),

                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('site.name')
                            ->label('Sitio')
                            ->badge(),
                        TextEntry::make('category.name')
                            ->label('Categoría')
                            ->badge()
                            ->color('info')
                            ->placeholder('Sin categoría'),
                        TextEntry::make('published')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Publicado' : 'Borrador')
                            ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
                        TextEntry::make('featured')
                            ->label('Destacado')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('author')
                            ->label('Autor')
                            ->placeholder('-'),
                        TextEntry::make('pub_date')
                            ->label('Fecha')
                            ->date('d M Y'),
                        TextEntry::make('tags')
                            ->label('Etiquetas')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
                        TextEntry::make('slug')
                            ->label('Slug')
                            ->color('gray')
                            ->copyable(),
                        TextEntry::make('updated_at')
                            ->label('Última edición')
                            ->since(),
                    ]),
            ]);
    }
}

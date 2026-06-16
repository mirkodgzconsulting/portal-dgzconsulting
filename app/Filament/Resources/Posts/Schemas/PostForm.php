<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Site;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->label('Sitio')
                    ->relationship('site', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('has_blog', true))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                TextInput::make('title')
                    ->label('Título')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Se autogenera del título si se deja vacío'),
                Textarea::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                MarkdownEditor::make('content')
                    ->label('Contenido')
                    ->columnSpanFull(),
                CuratorPicker::make('cover_image')
                    ->label('Imagen de portada')
                    ->disk('r2')
                    ->directory(fn (Get $get): string => 'posts/'.(Site::find($get('site_id'))?->slug ?? 'misc'))
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->maxSize(5120),
                TagsInput::make('tags')
                    ->label('Etiquetas'),
                TextInput::make('author')
                    ->label('Autor')
                    ->placeholder('Pablo Estevan / CONKRET'),
                DatePicker::make('pub_date')
                    ->label('Fecha de publicación')
                    ->required()
                    ->default(now()),
                Toggle::make('published')
                    ->label('Publicado')
                    ->default(false),
                Toggle::make('featured')
                    ->label('Destacado')
                    ->default(false),
            ]);
    }
}

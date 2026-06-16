<?php

namespace App\Filament\Cliente\Resources\Posts\Schemas;

use App\Models\Site;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->label('Sitio')
                    ->relationship('site', 'name', modifyQueryUsing: fn (Builder $query) => $query
                        ->where('client_id', Auth::guard('client')->id())
                        ->where('has_blog', true))
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
                TextInput::make('cover_image')
                    ->label('Imagen de portada (URL)')
                    ->placeholder('https://res.cloudinary.com/... o https://pub-xxx.r2.dev/...')
                    ->url()
                    ->helperText('Sube la imagen en "Mis Imágenes", copia la URL y pégala aquí')
                    ->columnSpanFull(),
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

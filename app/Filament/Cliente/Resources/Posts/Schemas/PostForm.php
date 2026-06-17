<?php

namespace App\Filament\Cliente\Resources\Posts\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Nomanur\FilamentSeoPro\Forms\SeoSection;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── Columna principal (izquierda) ──────────────────────────
                Section::make()
                    ->columnSpan(2)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('site_id')
                                    ->label('Sitio')
                                    ->relationship('site', 'name', modifyQueryUsing: fn (Builder $query) => $query
                                        ->where('client_id', Auth::guard('client')->id() ?? Auth::guard('client_user')->user()?->client_id)
                                        ->where('has_blog', true))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),
                                Select::make('category_id')
                                    ->label('Categoría')
                                    ->relationship('category', 'name', modifyQueryUsing: fn (Builder $query, $get) => $query->where('site_id', $get('site_id')))
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Sin categoría'),
                            ]),
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->hidden(),
                        Textarea::make('description')
                            ->hidden()
                            ->dehydratedWhenHidden(),
                        RichEditor::make('content')
                            ->label('Contenido')
                            ->columnSpanFull()
                            ->live(debounce: 1000)
                            ->extraInputAttributes(['style' => 'min-height: 300px']),
                        Placeholder::make('word_count')
                            ->label('')
                            ->columnSpanFull()
                            ->content(function ($get) {
                                $text = strip_tags((string) $get('content'));
                                $words = $text ? str_word_count($text) : 0;
                                $minutes = max(1, (int) ceil($words / 200));
                                return "{$words} palabras · {$minutes} min de lectura";
                            }),
                    ]),

                // ── Sidebar (derecha) ──────────────────────────────────────
                Section::make()
                    ->columnSpan(1)
                    ->extraAttributes(['class' => 'post-sidebar'])
                    ->schema([
                        Toggle::make('published')
                            ->label('Publicado')
                            ->default(false),
                        Toggle::make('featured')
                            ->label('Destacado')
                            ->default(false),
                        DatePicker::make('pub_date')
                            ->label('Fecha de publicación')
                            ->required()
                            ->default(now()),
                        TextInput::make('author')
                            ->label('Autor')
                            ->default(fn () => Auth::guard('client')->user()?->name ?? Auth::guard('client_user')->user()?->name)
                            ->placeholder(fn () => Auth::guard('client')->user()?->name ?? Auth::guard('client_user')->user()?->name),
                        TagsInput::make('tags')
                            ->label('Etiquetas'),
                    ]),

                // ── Imagen (ancho completo) ────────────────────────────────
                Section::make('Imagen de portada')
                    ->columnSpan(2)
                    ->schema([
                        ViewField::make('cover_preview')
                            ->view('filament.forms.components.cover-preview')
                            ->label(''),
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection('cover')
                            ->label('Subir nueva imagen')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->helperText('JPG, PNG, WebP — máx. 5 MB')
                            ->disk('r2')
                            ->visibility('public'),
                        Actions::make([
                            Action::make('browse_media')
                                ->label('Elegir de biblioteca')
                                ->icon('heroicon-o-photo')
                                ->color('gray')
                                ->modalHeading('Featured Image')
                                ->modalWidth('5xl')
                                ->modalContent(fn () => view('livewire.media-picker-inline'))
                                ->modalSubmitAction(false)
                                ->modalCancelActionLabel('Cerrar'),
                        ]),
                        TextInput::make('cover_image')
                            ->label('URL de imagen (desde biblioteca)')
                            ->helperText('Se llena automáticamente al seleccionar de la biblioteca')
                            ->url()
                            ->hidden()
                            ->dehydratedWhenHidden(),
                    ]),

                // ── SEO (ancho completo) ───────────────────────────────────
                Section::make('SEO (opcional)')
                    ->columnSpan(3)
                    ->collapsed()
                    ->collapsible()
                    ->schema([
                        SeoSection::make(),
                    ]),
            ]);
    }
}

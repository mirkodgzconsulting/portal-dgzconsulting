<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Nomanur\FilamentSeoPro\Forms\SeoSection;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name', modifyQueryUsing: fn (Builder $query, $get) => $query->where('site_id', $get('site_id')))
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin categoría')
                    ->createOptionForm([
                        TextInput::make('name')->label('Nombre')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')->label('Slug')->required(),
                    ])
                    ->createOptionUsing(function (array $data, $get) {
                        $data['site_id'] = $get('site_id');
                        return \App\Models\Category::create($data)->id;
                    }),
                TextInput::make('title')
                    ->label('Título')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Se autogenera del título si se deja vacío'),
                Textarea::make('description')
                    ->label('Resumen / Meta descripción')
                    ->helperText('Texto corto que aparece en Google y al compartir en redes. Máx. 160 caracteres.')
                    ->maxLength(160)
                    ->required()
                    ->columnSpanFull(),
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

                Grid::make(10)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Imagen de portada')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->helperText('JPG, PNG, WebP — máx. 5 MB')
                            ->columnSpan(3)
                            ->disk('local')
                            ->visibility('public')
                            ->saveUploadedFileUsing(function ($file, $get) {
                                $site = \App\Models\Site::find($get('site_id'));
                                $siteSlug = $site?->slug ?? 'general';
                                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                                $filename = Str::uuid() . '.' . $ext;
                                $path = "posts/{$siteSlug}/{$filename}";

                                Storage::disk('r2')->put($path, file_get_contents($file->getRealPath()), 'public');

                                return Storage::disk('r2')->url($path);
                            })
                            ->afterStateHydrated(function (FileUpload $component, $state) {
                                if ($state && str_starts_with($state, 'http')) {
                                    $component->state(null);
                                }
                            })
                            ->dehydrateStateUsing(function ($state, $get, $record) {
                                if (empty($state) && $record) {
                                    return $record->cover_image;
                                }
                                return $state;
                            }),

                        TextInput::make('cover_image_url')
                            ->label('O pegar URL externa (Cloudinary, R2, etc.)')
                            ->placeholder('https://res.cloudinary.com/... o https://pub-xxx.r2.dev/...')
                            ->url()
                            ->helperText('Si pegas una URL aquí, se usará como imagen de portada')
                            ->columnSpan(7)
                            ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                if ($record && $record->cover_image && str_starts_with($record->cover_image, 'http')) {
                                    $component->state($record->cover_image);
                                }
                            })
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (filled($state)) {
                                    $set('cover_image', $state);
                                }
                            }),
                    ]),

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

                SeoSection::make(),
            ]);
    }
}

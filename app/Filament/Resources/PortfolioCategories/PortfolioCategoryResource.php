<?php

namespace App\Filament\Resources\PortfolioCategories;

use App\Models\PortfolioCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioCategoryResource extends Resource
{
    protected static ?string $model = PortfolioCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'geist-image';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenido';
    }

    protected static ?string $label = 'Portfolio';

    protected static ?string $pluralLabel = 'Portfolio';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('Sitio')
                ->relationship('site', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('has_portfolio', true))
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')
                ->label('Slug')
                ->required(),
            Textarea::make('description')
                ->label('Descripción')
                ->rows(2),
            FileUpload::make('cover_image')
                ->label('Imagen de portada')
                ->image()
                ->maxSize(5120)
                ->disk('local')
                ->visibility('public')
                ->saveUploadedFileUsing(function ($file, $get) {
                    $site = \App\Models\Site::find($get('site_id'));
                    $siteSlug = $site?->slug ?? 'general';
                    $filename = Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                    $path = "portfolio/{$siteSlug}/covers/{$filename}";
                    Storage::disk('r2')->put($path, file_get_contents($file->getRealPath()), 'public');
                    return Storage::disk('r2')->url($path);
                }),
            TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('site.client.name')->label('Cliente')->searchable(),
                TextColumn::make('site.name')->label('Sitio')->searchable(),
                ImageColumn::make('cover_image')->label('Portada')->circular(),
                TextColumn::make('name')->label('Categoría')->searchable(),
                TextColumn::make('items_count')->label('Imágenes')->counts('items'),
                TextColumn::make('sort_order')->label('Orden')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PortfolioItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolioCategories::route('/'),
            'create' => Pages\CreatePortfolioCategory::route('/create'),
            'edit' => Pages\EditPortfolioCategory::route('/{record}/edit'),
        ];
    }
}

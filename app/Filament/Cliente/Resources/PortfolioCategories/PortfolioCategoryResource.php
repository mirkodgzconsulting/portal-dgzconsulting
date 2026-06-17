<?php

namespace App\Filament\Cliente\Resources\PortfolioCategories;

use App\Models\PortfolioCategory;
use App\Models\Site;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioCategoryResource extends Resource
{
    protected static ?string $model = PortfolioCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'geist-image';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Portfolio';
    }

    protected static ?string $label = 'Categoría';

    protected static ?string $pluralLabel = 'Mi Portfolio';

    public static function shouldRegisterNavigation(): bool
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        if (! $clientId) return false;

        return Site::where('client_id', $clientId)
            ->where('has_portfolio', true)
            ->exists();
    }

    public static function getEloquentQuery(): Builder
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        return parent::getEloquentQuery()
            ->whereHas('site', fn (Builder $q) => $q
                ->where('client_id', $clientId)
                ->where('has_portfolio', true));
    }

    public static function form(Schema $schema): Schema
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        return $schema->components([
            Select::make('site_id')
                ->label('Sitio')
                ->relationship('site', 'name', modifyQueryUsing: fn (Builder $query) => $query
                    ->where('client_id', $clientId)
                    ->where('has_portfolio', true))
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
                ->hidden(),
            TextInput::make('sort_order')
                ->label('Orden en homepage')
                ->numeric()
                ->default(0)
                ->helperText('Número más bajo aparece primero (1, 2, 3...)'),
            Textarea::make('description')
                ->label('Descripción')
                ->rows(2),
            FileUpload::make('cover_image')
                ->label('Imagen de portada')
                ->image()
                ->maxSize(5120)
                ->disk('local')
                ->visibility('public')
                ->helperText(fn ($record) => $record?->cover_image
                    ? new HtmlString('<img src="' . e($record->cover_image) . '" style="max-height:120px;object-fit:contain;border-radius:6px;margin-top:8px;">')
                    : null)
                ->saveUploadedFileUsing(function ($file, $get) {
                    $site = Site::find($get('site_id'));
                    $siteSlug = $site?->slug ?? 'general';
                    $filename = Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                    $path = "portfolio/{$siteSlug}/covers/{$filename}";
                    Storage::disk('r2')->put($path, file_get_contents($file->getRealPath()), 'public');
                    return Storage::disk('r2')->url($path);
                })
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    if ($state && str_starts_with($state, 'http')) {
                        $component->state(null);
                    }
                })
                ->dehydrateStateUsing(function ($state, $record) {
                    if (empty($state) && $record) {
                        return $record->cover_image;
                    }
                    return $state;
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')->label('Orden')->sortable(),
                ImageColumn::make('cover_image')->label('Portada')->circular(),
                TextColumn::make('name')->label('Categoría')->searchable(),
                TextColumn::make('items_count')->label('Imágenes')->counts('items'),
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

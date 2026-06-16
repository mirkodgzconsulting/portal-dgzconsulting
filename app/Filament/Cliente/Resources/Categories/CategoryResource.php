<?php

namespace App\Filament\Cliente\Resources\Categories;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedTag;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenido';
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Categoría';

    protected static ?string $pluralLabel = 'Categorías';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('Sitio')
                ->relationship('site', 'name', modifyQueryUsing: fn (Builder $query) => $query
                    ->where('client_id', Auth::guard('client')->id() ?? Auth::guard('client_user')->user()?->client_id)
                    ->where('has_blog', true))
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
                ->required()
                ->helperText('Se autogenera del nombre'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('site', fn ($q) => $q
                ->where('client_id', Auth::guard('client')->id() ?? Auth::guard('client_user')->user()?->client_id)
            ))
            ->columns([
                TextColumn::make('site.name')->label('Sitio')->badge(),
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->color('gray'),
                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts')
                    ->badge()
                    ->color('success'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

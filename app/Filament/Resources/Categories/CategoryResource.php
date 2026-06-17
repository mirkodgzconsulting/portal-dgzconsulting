<?php

namespace App\Filament\Resources\Categories;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static \BackedEnum|string|null $navigationIcon = "phosphor-tag-light";

    public static function getNavigationGroup(): ?string
    {
        return 'Contenido';
    }

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Categoría';

    protected static ?string $pluralLabel = 'Categorías';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('Sitio')
                ->relationship('site', 'name', fn ($query) => $query->where('has_blog', true))
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
            TextInput::make('slug')
                ->label('Slug')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.client.name')->label('Cliente')->searchable(),
                TextColumn::make('site.name')->label('Sitio')->searchable(),
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

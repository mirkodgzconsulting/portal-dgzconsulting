<?php

namespace App\Filament\Resources\PortfolioCategories\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Imágenes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image_url')
                ->label('Imagen')
                ->image()
                ->required()
                ->maxSize(10240)
                ->disk('local')
                ->visibility('public')
                ->saveUploadedFileUsing(function ($file) {
                    $category = $this->getOwnerRecord();
                    $site = $category->site;
                    $siteSlug = $site?->slug ?? 'general';
                    $catSlug = $category->slug;
                    $filename = Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                    $path = "portfolio/{$siteSlug}/{$catSlug}/{$filename}";
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
                        return $record->image_url;
                    }
                    return $state;
                }),
            TextInput::make('title')
                ->label('Título (opcional)'),
            Textarea::make('description')
                ->label('Descripción (opcional)')
                ->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_url')->label('Imagen')->size(80),
                TextColumn::make('title')->label('Título')->placeholder('Sin título'),
                TextColumn::make('sort_order')->label('Orden'),
            ])
            ->headerActions([CreateAction::make()->label('Agregar imagen')])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}

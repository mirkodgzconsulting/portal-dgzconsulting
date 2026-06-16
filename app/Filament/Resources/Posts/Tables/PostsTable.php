<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Select;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('site.name')
                    ->label('Sitio')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->description(fn ($record) => $record->description ? str($record->description)->limit(60) : null),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('info')
                    ->placeholder('Sin categoría'),
                TextColumn::make('pub_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(),
                IconColumn::make('featured')
                    ->label('Destacado')
                    ->boolean(),
            ])
            ->defaultSort('pub_date', 'desc')
            ->defaultPaginationPageOption(25)
            ->filters([
                SelectFilter::make('site_id')
                    ->label('Sitio')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('published')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Publicados')
                    ->falseLabel('Borradores'),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->placeholder('Todas las categorías')
                    ->options(fn () => Category::orderBy('name')->pluck('name', 'id')),
                TernaryFilter::make('category_id')
                    ->label('¿Tiene categoría?')
                    ->placeholder('Todos')
                    ->trueLabel('Con categoría')
                    ->falseLabel('Sin categoría')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('category_id'),
                        false: fn (Builder $q) => $q->whereNull('category_id'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicar')
                    ->beforeReplicaSaved(function ($replica) {
                        $replica->title = 'Copia de ' . $replica->title;
                        $replica->slug = null;
                        $replica->published = false;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign_category')
                        ->label('Asignar categoría')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('category_id')
                                ->label('Categoría')
                                ->placeholder('Sin categoría')
                                ->options(fn () => Category::orderBy('name')->pluck('name', 'id'))
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['category_id' => $data['category_id'] ?? null]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

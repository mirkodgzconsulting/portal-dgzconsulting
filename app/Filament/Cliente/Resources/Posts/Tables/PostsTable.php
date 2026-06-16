<?php

namespace App\Filament\Cliente\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label('Sitio')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('pub_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(),
                TextColumn::make('tags')
                    ->label('Etiquetas')
                    ->badge(),
            ])
            ->defaultPaginationPageOption(25)
            ->filters([
                TernaryFilter::make('published')
                    ->label('Publicado'),
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

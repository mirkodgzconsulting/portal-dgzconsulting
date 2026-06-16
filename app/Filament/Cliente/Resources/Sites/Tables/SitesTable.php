<?php

namespace App\Filament\Cliente\Resources\Sites\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('domain')
                    ->label('Dominio')
                    ->placeholder('-'),
                TextColumn::make('cms_type')
                    ->label('CMS')
                    ->placeholder('-'),
                TextColumn::make('hosting_provider')
                    ->label('Hosting')
                    ->placeholder('-'),
                IconColumn::make('has_blog')
                    ->label('Blog')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->paginated(false);
    }
}

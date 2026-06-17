<?php

namespace App\Curator;

use App\Models\Client;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\Facades\Curator;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        $livewire = $table->getLivewire();

        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        return $table
            ->modifyQueryUsing(function ($query) use ($clientId) {
                if ($clientId) {
                    $query->where('client_id', $clientId);
                }
            })
            ->columns(
                $livewire->layoutView === 'grid'
                    ? static::getGridColumns()
                    : static::getListColumns(),
            )
            ->filters($clientId ? [] : [
                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->options(Client::orderBy('name')->pluck('name', 'id'))
                    ->placeholder('Todos los clientes'),
            ])
            ->searchable(['title', 'caption', 'description'])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->contentGrid(function () use ($livewire): ?array {
                if ($livewire->layoutView === 'grid') {
                    return [
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4,
                    ];
                }

                return null;
            })
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([6, 12, 24, 48, 'all'])
            ->recordUrl(null);
    }

    public static function getListColumns(): array
    {
        return [
            CuratorColumn::make('url')
                ->label(trans('curator::tables.columns.url'))
                ->imageSize(40)
                ->alignCenter()
                ->width('60px'),
            TextColumn::make('client.name')
                ->label('Cliente')
                ->badge()
                ->color('info')
                ->sortable(),
            TextColumn::make('name')
                ->label(trans('curator::tables.columns.name'))
                ->limit(50)
                ->tooltip(fn ($record) => $record->name)
                ->searchable()
                ->sortable(),
            TextColumn::make('ext')
                ->label(trans('curator::tables.columns.ext'))
                ->badge()
                ->color('gray')
                ->width('60px')
                ->sortable(),
            TextColumn::make('size')
                ->label(trans('curator::tables.columns.size'))
                ->formatStateUsing(fn ($record): string => Curator::sizeForHumans($record->size))
                ->width('80px')
                ->sortable(),
            TextColumn::make('created_at')
                ->label(trans('curator::tables.columns.created_at'))
                ->date('d/m/Y')
                ->width('100px')
                ->sortable(),
        ];
    }

    public static function getGridColumns(): array
    {
        return [
            View::make('curator::components.tables.grid-column'),
            TextColumn::make('name')
                ->label(trans('curator::tables.columns.name'))
                ->searchable()
                ->sortable()
                ->extraCellAttributes(['class' => 'dgz-hidden-cell']),
            TextColumn::make('size')
                ->label(trans('curator::tables.columns.size'))
                ->formatStateUsing(fn ($record): string => Curator::sizeForHumans($record->size))
                ->sortable()
                ->extraCellAttributes(['class' => 'dgz-hidden-cell']),
            TextColumn::make('ext')
                ->sortable()
                ->extraCellAttributes(['class' => 'dgz-hidden-cell']),
            TextColumn::make('created_at')
                ->sortable()
                ->extraCellAttributes(['class' => 'dgz-hidden-cell']),
        ];
    }
}

<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\Post;
use App\Models\Site;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentPostsWidget extends TableWidget
{
    protected static ?string $heading = 'Últimos Posts';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        return $table
            ->query(
                Post::query()
                    ->whereHas('site', fn (Builder $q) => $q->where('client_id', $clientId))
                    ->with(['site', 'category'])
                    ->latest('pub_date')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->limit(50)
                    ->url(fn ($record) => route('filament.cliente.resources.posts.edit', $record)),
                TextColumn::make('site.name')
                    ->label('Sitio')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('success')
                    ->placeholder('Sin categoría'),
                IconColumn::make('published')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-pencil'),
                TextColumn::make('pub_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}

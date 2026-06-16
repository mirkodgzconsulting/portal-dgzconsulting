<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=0F65E6&background=dbeafe&bold=true&size=64')
                    ->size(40),

                TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email),

                TextColumn::make('sites_summary')
                    ->label('Sitios')
                    ->getStateUsing(fn ($record) => $record->sites->pluck('name')->join(', '))
                    ->formatStateUsing(function ($state, $record) {
                        $sites = $record->sites;
                        if ($sites->isEmpty()) return new HtmlString('<span class="text-gray-400 text-xs">—</span>');

                        $visible = $sites->take(2);
                        $remaining = $sites->count() - 2;

                        $chips = $visible->map(fn ($site) =>
                            '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">'
                            . e($site->name) . '</span>'
                        )->join(' ');

                        if ($remaining > 0) {
                            $allNames = $sites->skip(2)->pluck('name')->join(', ');
                            $chips .= ' <span title="' . e($allNames) . '" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 cursor-default">+' . $remaining . '</span>';
                        }

                        return new HtmlString('<div class="flex flex-wrap gap-1">' . $chips . '</div>');
                    })
                    ->html()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('sites', fn ($q) => $q->where('name', 'like', "%{$search}%"))),

                TextColumn::make('domains_summary')
                    ->label('Dominios')
                    ->getStateUsing(fn ($record) => $record->sites->pluck('domain')->filter()->join(', '))
                    ->formatStateUsing(function ($state, $record) {
                        $domains = $record->sites->pluck('domain')->filter()->values();
                        if ($domains->isEmpty()) return new HtmlString('<span class="text-gray-400 text-xs">—</span>');

                        $visible = $domains->take(2);
                        $remaining = $domains->count() - 2;

                        $links = $visible->map(fn ($domain) =>
                            '<a href="https://' . e($domain) . '" target="_blank" class="text-primary-600 hover:underline text-xs whitespace-nowrap">'
                            . e($domain) . '</a>'
                        )->join('<br>');

                        if ($remaining > 0) {
                            $rest = $domains->skip(2)->join(', ');
                            $links .= '<br><span title="' . e($rest) . '" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 cursor-default">+' . $remaining . '</span>';
                        }

                        return new HtmlString('<div class="flex flex-col gap-0.5">' . $links . '</div>');
                    })
                    ->html()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('sites', fn ($q) => $q->where('domain', 'like', "%{$search}%"))),

                TextColumn::make('active_subscription')
                    ->label('Plan')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $sub = $record->sites()
                            ->with(['subscriptions' => fn ($q) => $q->where('status', 'active')->latest('renewal_date')])
                            ->get()
                            ->flatMap(fn ($site) => $site->subscriptions)
                            ->first();
                        return $sub?->service_type ?? 'Sin plan';
                    })
                    ->color(fn ($state) => match (true) {
                        str_contains(strtolower((string) $state), 'mantenimiento') => 'success',
                        str_contains(strtolower((string) $state), 'hosting') => 'info',
                        str_contains(strtolower((string) $state), 'seo') => 'warning',
                        $state === 'Sin plan' => 'gray',
                        default => 'primary',
                    }),

                TextColumn::make('renewal_date')
                    ->label('Vence')
                    ->getStateUsing(function ($record) {
                        $sub = $record->sites()
                            ->with(['subscriptions' => fn ($q) => $q->where('status', 'active')->orderBy('renewal_date')])
                            ->get()
                            ->flatMap(fn ($site) => $site->subscriptions)
                            ->sortBy('renewal_date')
                            ->first();
                        return $sub?->renewal_date;
                    })
                    ->date('d/m/Y')
                    ->sortable(false)
                    ->color(fn ($state) => match (true) {
                        $state && Carbon::parse($state)->isPast() => 'danger',
                        $state && Carbon::parse($state)->diffInDays(now()) <= 30 => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match (true) {
                        $state && Carbon::parse($state)->isPast() => 'heroicon-m-exclamation-triangle',
                        $state && Carbon::parse($state)->diffInDays(now()) <= 30 => 'heroicon-m-clock',
                        default => null,
                    }),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('active')
                    ->label('Activo'),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('active')
                    ->label('Activo')
                    ->default(true),

                SelectFilter::make('subscription_status')
                    ->label('Estado suscripción')
                    ->options([
                        'active' => 'Activa',
                        'expired' => 'Vencida',
                        'cancelled' => 'Cancelada',
                    ])
                    ->query(fn ($query, $data) => $data['value']
                        ? $query->whereHas('sites.subscriptions', fn ($q) => $q->where('status', $data['value']))
                        : $query),

                SelectFilter::make('renewal_soon')
                    ->label('Vencimiento')
                    ->options([
                        '30' => 'Próximos 30 días',
                        '60' => 'Próximos 60 días',
                        'overdue' => 'Vencidos',
                    ])
                    ->query(function ($query, $data) {
                        if (! $data['value']) return $query;
                        return match ($data['value']) {
                            'overdue' => $query->whereHas('sites.subscriptions', fn ($q) => $q->where('status', 'active')->where('renewal_date', '<', now())),
                            default => $query->whereHas('sites.subscriptions', fn ($q) => $q->where('status', 'active')->whereBetween('renewal_date', [now(), now()->addDays((int) $data['value'])])),
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
                Action::make('copy_access')
                    ->label('')
                    ->icon('heroicon-m-clipboard-document')
                    ->tooltip('Copiar URL de acceso')
                    ->color('gray')
                    ->action(fn () => null)
                    ->extraAttributes(fn ($record) => [
                        'x-data' => '{}',
                        'x-on:click' => 'navigator.clipboard.writeText("' . e($record->sites->first()?->admin_url ?? '') . '"); $tooltip("¡Copiado!", { timeout: 1500 })',
                    ])
                    ->visible(fn ($record) => (bool) $record->sites->first()?->admin_url),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100]);
    }
}

<?php

namespace App\Filament\Cliente\Resources\Subscriptions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label('Sitio'),
                TextColumn::make('service_type')
                    ->label('Servicio'),
                TextColumn::make('price')
                    ->label('Precio')
                    ->formatStateUsing(fn ($record) => match ($record->currency) {
                        'EUR' => '€',
                        'USD', 'ARS', 'COP', 'MXN' => '$',
                        'PEN' => 'S/',
                        'GBP' => '£',
                        default => '',
                    } . ' ' . number_format($record->price, 2)),
                TextColumn::make('billing_cycle')
                    ->label('Ciclo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly' => 'Mensual',
                        'quarterly' => 'Trimestral',
                        'yearly' => 'Anual',
                        'one_time' => 'Único',
                        default => $state,
                    }),
                TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'transfer' => 'Transferencia',
                        'cash' => 'Efectivo',
                        default => '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'stripe' => 'info',
                        'paypal' => 'warning',
                        'transfer' => 'gray',
                        'cash' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('renewal_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pagado' => 'Pagado',
                        'por_vencer' => 'Por vencer',
                        'vencido' => 'Vencido',
                        'fuera_de_servicio' => 'Fuera de servicio',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pagado' => 'success',
                        'por_vencer' => 'warning',
                        'vencido' => 'danger',
                        'fuera_de_servicio' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('renewal_date');
    }
}

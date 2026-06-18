<?php

namespace App\Filament\Cliente\Resources\Subscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make()
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('service_type')
                            ->label('Servicio')
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('site.name')
                            ->label('Sitio')
                            ->badge(),
                        TextEntry::make('price')
                            ->label('Precio')
                            ->formatStateUsing(fn ($record) => match ($record->currency) {
                                'EUR' => '€',
                                'USD', 'ARS', 'COP', 'MXN' => '$',
                                'PEN' => 'S/',
                                'GBP' => '£',
                                default => '',
                            } . ' ' . number_format($record->price, 2) . ' ' . $record->currency),
                        TextEntry::make('billing_cycle')
                            ->label('Ciclo')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'monthly' => 'Mensual',
                                'quarterly' => 'Trimestral',
                                'yearly' => 'Anual',
                                'one_time' => 'Pago único',
                                default => $state,
                            }),
                        TextEntry::make('status')
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
                        TextEntry::make('payment_method')
                            ->label('Método de pago')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'stripe' => 'Stripe',
                                'paypal' => 'PayPal',
                                'transfer' => 'Transferencia',
                                'cash' => 'Efectivo',
                                default => 'No definido',
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'stripe' => 'info',
                                'paypal' => 'warning',
                                'transfer' => 'gray',
                                'cash' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('start_date')
                            ->label('Fecha de inicio')
                            ->date('d M Y'),
                        TextEntry::make('renewal_date')
                            ->label('Próximo vencimiento')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('payment_link')
                            ->label('Pagar')
                            ->visible(fn ($record) => filled($record->payment_link))
                            ->formatStateUsing(fn () => new HtmlString('<span style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#0F65E6;color:white;border-radius:6px;font-size:14px;font-weight:500;">Pagar ahora →</span>'))
                            ->url(fn ($record) => $record->payment_link, shouldOpenInNewTab: true),
                    ]),
            ]);
    }
}

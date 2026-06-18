<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->label('Sitio')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('service_type')
                    ->label('Servicio')
                    ->placeholder('Hosting + Dominio, Mantenimiento...')
                    ->required(),
                Grid::make(3)
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio')
                            ->required()
                            ->numeric(),
                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'EUR' => '€ EUR',
                                'USD' => '$ USD',
                                'PEN' => 'S/ PEN',
                                'ARS' => '$ ARS',
                                'COP' => '$ COP',
                                'MXN' => '$ MXN',
                                'GBP' => '£ GBP',
                            ])
                            ->default('EUR')
                            ->required(),
                        Select::make('billing_cycle')
                            ->label('Ciclo')
                            ->options([
                                'monthly' => 'Mensual',
                                'quarterly' => 'Trimestral',
                                'yearly' => 'Anual',
                                'one_time' => 'Pago único',
                            ])
                            ->required(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('payment_method')
                            ->label('Método de pago')
                            ->options([
                                'stripe' => 'Stripe',
                                'paypal' => 'PayPal',
                                'transfer' => 'Transferencia bancaria',
                                'cash' => 'Efectivo',
                            ])
                            ->live(),
                        TextInput::make('payment_link')
                            ->label('Link de pago')
                            ->url()
                            ->placeholder('https://buy.stripe.com/...')
                            ->visible(fn ($get) => in_array($get('payment_method'), ['stripe', 'paypal'])),
                    ]),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pagado' => 'Pagado',
                        'por_vencer' => 'Por vencer',
                        'vencido' => 'Vencido',
                        'fuera_de_servicio' => 'Fuera de servicio',
                    ])
                    ->default('pagado')
                    ->required(),
                Grid::make(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Fecha de inicio')
                            ->required(),
                        DatePicker::make('renewal_date')
                            ->label('Próximo vencimiento'),
                    ]),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}

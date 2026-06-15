<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Select::make('billing_cycle')
                    ->label('Ciclo de facturación')
                    ->options([
                        'monthly' => 'Mensual',
                        'yearly' => 'Anual',
                    ])
                    ->required(),
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
                DatePicker::make('start_date')
                    ->label('Fecha de inicio')
                    ->required(),
                DatePicker::make('renewal_date')
                    ->label('Próximo vencimiento'),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}

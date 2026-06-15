<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('site.client.name')
                    ->label('Cliente'),
                TextEntry::make('site.name')
                    ->label('Sitio'),
                TextEntry::make('service_type')
                    ->label('Servicio'),
                TextEntry::make('price')
                    ->label('Precio')
                    ->money('EUR'),
                TextEntry::make('billing_cycle')
                    ->label('Ciclo de facturación')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly' => 'Mensual',
                        'yearly' => 'Anual',
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
                TextEntry::make('start_date')
                    ->label('Fecha de inicio')
                    ->date(),
                TextEntry::make('renewal_date')
                    ->label('Próximo vencimiento')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->label('Notas')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

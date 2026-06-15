<?php

namespace App\Filament\Resources\Sites\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_type')
            ->columns([
                TextColumn::make('service_type')
                    ->label('Servicio')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('billing_cycle')
                    ->label('Ciclo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly' => 'Mensual',
                        'yearly' => 'Anual',
                        default => $state,
                    }),
                TextColumn::make('renewal_date')
                    ->label('Próximo vencimiento')
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
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

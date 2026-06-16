<?php

namespace App\Filament\Cliente\Resources\Subscriptions;

use App\Filament\Cliente\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Cliente\Resources\Subscriptions\Pages\ViewSubscription;
use App\Filament\Cliente\Resources\Subscriptions\Schemas\SubscriptionInfolist;
use App\Filament\Cliente\Resources\Subscriptions\Tables\SubscriptionsTable;
use App\Models\Subscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Mis Suscripciones';

    protected static ?string $modelLabel = 'suscripción';

    protected static ?string $pluralModelLabel = 'mis suscripciones';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('client')->check();
    }

    public static function getEloquentQuery(): Builder
    {
        $clientId = Auth::guard('client')->id() ?? 0;

        return parent::getEloquentQuery()
            ->whereHas('site', function (Builder $query) use ($clientId): void {
                $query->where('client_id', $clientId);
            });
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
        ];
    }
}

<?php

namespace App\Filament\Cliente\Resources\Sites;

use App\Filament\Cliente\Resources\Sites\Pages\ListSites;
use App\Filament\Cliente\Resources\Sites\Pages\ViewSite;
use App\Filament\Cliente\Resources\Sites\Schemas\SiteInfolist;
use App\Filament\Cliente\Resources\Sites\Tables\SitesTable;
use App\Models\Site;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Mi Sitio';

    protected static ?string $modelLabel = 'sitio';

    protected static ?string $pluralModelLabel = 'mis sitios';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', Auth::guard('client')->id());
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SitesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSites::route('/'),
            'view' => ViewSite::route('/{record}'),
        ];
    }
}

<?php

namespace App\Filament\Cliente\Resources\ClientUsers;

use App\Models\ClientUser;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ClientUserResource extends Resource
{
    protected static ?string $model = ClientUser::class;

    protected static \BackedEnum|string|null $navigationIcon = LucideIcon::UsersRound;

    public static function getNavigationGroup(): ?string
    {
        return 'Configuración';
    }

    protected static ?string $label = 'Editor';

    protected static ?string $pluralLabel = 'Mis Editores';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', Auth::guard('client')->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required(),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),
            TextInput::make('password')
                ->label('Contraseña (dejar vacío para no cambiar)')
                ->password()
                ->revealable()
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
            Toggle::make('active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                IconColumn::make('active')->label('Activo')->boolean(),
                TextColumn::make('created_at')->label('Creado')->date()->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientUsers::route('/'),
            'create' => Pages\CreateClientUser::route('/create'),
            'edit' => Pages\EditClientUser::route('/{record}/edit'),
        ];
    }
}

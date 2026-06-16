<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitesRelationManager extends RelationManager
{
    protected static string $relationship = 'sites';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del sitio')
                    ->required(),
                TextInput::make('domain')
                    ->label('Dominio')
                    ->placeholder('modelooctatrico.com'),
                TextInput::make('admin_url')
                    ->label('URL de administración')
                    ->url()
                    ->placeholder('https://modelooctatrico.com/wp-admin'),
                TextInput::make('cms_type')
                    ->label('Tipo de CMS')
                    ->placeholder('WordPress, Astro, Strapi...'),
                TextInput::make('hosting_provider')
                    ->label('Proveedor de hosting')
                    ->placeholder('SiteGround, Hetzner...'),
                Toggle::make('has_blog')
                    ->label('Tiene módulo de blog'),

                Section::make('Acceso al CMS')
                    ->description('Credenciales guardadas cifradas. Solo visibles en este panel.')
                    ->columns(2)
                    ->components([
                        TextInput::make('cms_username')
                            ->label('Usuario'),
                        TextInput::make('cms_password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Sitio')
                    ->searchable(),
                TextColumn::make('domain')
                    ->label('Dominio')
                    ->searchable(),
                TextColumn::make('hosting_provider')
                    ->label('Hosting'),
                IconColumn::make('has_blog')
                    ->label('Blog')
                    ->boolean(),
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

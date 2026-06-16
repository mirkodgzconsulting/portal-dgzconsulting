<?php

namespace App\Filament\Resources\Sites\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (bool) $ownerRecord->has_blog;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Se autogenera del título si se deja vacío'),
                Textarea::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                MarkdownEditor::make('content')
                    ->label('Contenido')
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->label('Imagen de portada')
                    ->image()
                    ->disk('r2')
                    ->directory('posts/'.$this->getOwnerRecord()->slug),
                TagsInput::make('tags')
                    ->label('Etiquetas'),
                TextInput::make('author')
                    ->label('Autor')
                    ->placeholder('Pablo Estevan / CONKRET'),
                DatePicker::make('pub_date')
                    ->label('Fecha de publicación')
                    ->required()
                    ->default(now()),
                Toggle::make('published')
                    ->label('Publicado')
                    ->default(false),
                Toggle::make('featured')
                    ->label('Destacado')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('pub_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(),
                IconColumn::make('featured')
                    ->label('Destacado')
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

<?php

namespace App\Filament\Cliente\Resources\Posts;

use App\Filament\Cliente\Resources\Posts\Pages\CreatePost;
use App\Filament\Cliente\Resources\Posts\Pages\EditPost;
use App\Filament\Cliente\Resources\Posts\Pages\ListPosts;
use App\Filament\Cliente\Resources\Posts\Pages\ViewPost;
use App\Filament\Cliente\Resources\Posts\Schemas\PostForm;
use App\Filament\Cliente\Resources\Posts\Schemas\PostInfolist;
use App\Filament\Cliente\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = "phosphor-file-text-light";

    protected static ?string $navigationLabel = 'Mis Posts';

    protected static ?string $modelLabel = 'post';

    protected static ?string $pluralModelLabel = 'posts';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenido';
    }

    public static function getEloquentQuery(): Builder
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        return parent::getEloquentQuery()
            ->whereHas('site', function (Builder $query) use ($clientId): void {
                $query->where('client_id', $clientId)
                    ->where('has_blog', true);
            });
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'view' => ViewPost::route('/{record}'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}

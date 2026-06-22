<?php

namespace App\Filament\Cliente\Resources\PortfolioCategories\RelationManagers;

use App\Models\Site;
use App\Services\PortfolioSeoGenerator;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Imágenes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image_url')
                ->label('Imagen')
                ->image()
                ->required(fn ($record) => $record === null)
                ->maxSize(10240)
                ->disk('local')
                ->visibility('public')
                ->saveUploadedFileUsing(function ($file) {
                    $category = $this->getOwnerRecord();
                    $site = $category->site;
                    $siteSlug = $site?->slug ?? 'general';
                    $catSlug = $category->slug;
                    $filename = Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                    $path = "portfolio/{$siteSlug}/{$catSlug}/{$filename}";
                    Storage::disk('r2')->put($path, file_get_contents($file->getRealPath()), 'public');
                    return Storage::disk('r2')->url($path);
                })
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    if ($state && str_starts_with($state, 'http')) {
                        $component->state(null);
                    }
                })
                ->dehydrateStateUsing(function ($state, $record) {
                    if (empty($state) && $record) {
                        return $record->image_url;
                    }
                    return $state;
                }),
            TextInput::make('title')
                ->label('Título (opcional)'),
            Textarea::make('description')
                ->label('Descripción (opcional)')
                ->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_url')->label('Imagen')->size(80),
                TextColumn::make('title')->label('Título')->placeholder('Sin título'),
                TextColumn::make('sort_order')->label('Orden'),
            ])
            ->headerActions([
                CreateAction::make()->label('Agregar imagen'),
                Action::make('generateItemsSeo')
                    ->label('Generar SEO imágenes (IA)')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('Genera descriptions SEO para todas las imágenes sin descripción en esta categoría. Puede tardar un minuto.')
                    ->action(function (PortfolioSeoGenerator $generator) {
                        if (! $generator->isConfigured()) {
                            \Filament\Notifications\Notification::make()
                                ->title('ANTHROPIC_API_KEY no configurada')
                                ->danger()
                                ->send();

                            return;
                        }

                        $category = $this->getOwnerRecord();
                        $items = $category->items()
                            ->where(function ($q) {
                                $q->whereNull('description')->orWhere('description', '');
                            })
                            ->orderBy('sort_order')
                            ->get();

                        if ($items->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Todas las imágenes ya tienen descripción')
                                ->info()
                                ->send();

                            return;
                        }

                        $updated = 0;
                        foreach ($items->chunk(20) as $chunk) {
                            try {
                                $descriptions = $generator->generateItemDescriptions($category, $chunk);
                                foreach ($chunk as $item) {
                                    if (empty($descriptions[$item->id])) {
                                        continue;
                                    }
                                    $item->update(['description' => $descriptions[$item->id]]);
                                    $updated++;
                                }
                            } catch (\Throwable $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error parcial al generar SEO')
                                    ->body($e->getMessage())
                                    ->warning()
                                    ->send();
                            }
                            usleep(500_000);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("SEO generado para {$updated} imágenes")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}

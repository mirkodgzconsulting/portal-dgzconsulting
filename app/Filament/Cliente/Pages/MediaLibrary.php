<?php

namespace App\Filament\Cliente\Pages;

use App\Models\Client;
use App\Models\Post;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\Site;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibrary extends Page
{
    use WithPagination, WithFileUploads;

    protected static \BackedEnum|string|null $navigationIcon = 'phosphor-image-light';

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenido';
    }

    protected string $view = 'filament.cliente.pages.media-library';

    public string $search = '';
    public string $viewMode = 'grid';
    public ?int $selectedMediaId = null;
    public $newFiles = [];

    public function getClientId(): ?int
    {
        return Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;
    }

    public function getClient(): ?Client
    {
        $clientId = $this->getClientId();
        return $clientId ? Client::find($clientId) : null;
    }

    public function getMediaProperty()
    {
        $clientId = $this->getClientId();
        if (! $clientId) return Media::query()->whereRaw('1=0')->paginate(24);

        $siteIds = Site::where('client_id', $clientId)->pluck('id');
        $postIds = Post::whereIn('site_id', $siteIds)->pluck('id');
        $portfolioCatIds = PortfolioCategory::whereIn('site_id', $siteIds)->pluck('id');
        $portfolioItemIds = PortfolioItem::whereIn('portfolio_category_id', $portfolioCatIds)->pluck('id');

        return Media::query()
            ->where(function ($query) use ($clientId, $postIds, $portfolioCatIds, $portfolioItemIds) {
                $query->where(function ($q) use ($clientId) {
                    $q->where('model_type', Client::class)->where('model_id', $clientId);
                })->orWhere(function ($q) use ($postIds) {
                    $q->where('model_type', Post::class)->whereIn('model_id', $postIds);
                })->orWhere(function ($q) use ($portfolioCatIds) {
                    $q->where('model_type', PortfolioCategory::class)->whereIn('model_id', $portfolioCatIds);
                })->orWhere(function ($q) use ($portfolioItemIds) {
                    $q->where('model_type', PortfolioItem::class)->whereIn('model_id', $portfolioItemIds);
                });
            })
            ->when($this->search, fn ($q) => $q->where('file_name', 'like', "%{$this->search}%"))
            ->orderByDesc('created_at')
            ->paginate(24);
    }

    public function getSelectedMedia(): ?Media
    {
        if (! $this->selectedMediaId) return null;
        return Media::find($this->selectedMediaId);
    }

    public function selectMedia(int $id): void
    {
        $this->selectedMediaId = $this->selectedMediaId === $id ? null : $id;
    }

    public function deleteMedia(): void
    {
        if ($this->selectedMediaId) {
            Media::find($this->selectedMediaId)?->delete();
            $this->selectedMediaId = null;
        }
    }

    public function updatedNewFiles(): void
    {
        $client = $this->getClient();
        if (! $client) return;

        foreach ($this->newFiles as $file) {
            $client->addMedia($file->getRealPath())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('library', 'r2');
        }

        $this->newFiles = [];
        $this->resetPage();
    }

    public function toggleView(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}

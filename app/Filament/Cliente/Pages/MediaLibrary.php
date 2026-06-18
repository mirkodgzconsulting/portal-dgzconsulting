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

    public static function getNavigationBadge(): ?string
    {
        $clientId = Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;

        if (! $clientId) return null;

        $siteIds = Site::where('client_id', $clientId)->pluck('id');
        $postIds = Post::whereIn('site_id', $siteIds)->pluck('id');
        $portfolioCatIds = PortfolioCategory::whereIn('site_id', $siteIds)->pluck('id');
        $portfolioItemIds = PortfolioItem::whereIn('portfolio_category_id', $portfolioCatIds)->pluck('id');

        $count = Media::where(function ($query) use ($clientId, $postIds, $portfolioCatIds, $portfolioItemIds) {
            $query->where(fn ($q) => $q->where('model_type', Client::class)->where('model_id', $clientId))
                ->orWhere(fn ($q) => $q->where('model_type', Post::class)->whereIn('model_id', $postIds))
                ->orWhere(fn ($q) => $q->where('model_type', PortfolioCategory::class)->whereIn('model_id', $portfolioCatIds))
                ->orWhere(fn ($q) => $q->where('model_type', PortfolioItem::class)->whereIn('model_id', $portfolioItemIds));
        })->count();

        return (string) $count;
    }

    protected string $view = 'filament.cliente.pages.media-library';

    public string $search = '';
    public string $viewMode = 'grid';
    public string $filter = 'all';
    public int $perPage = 24;
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

    public function getFilters(): array
    {
        $clientId = $this->getClientId();
        if (! $clientId) return [];

        $siteIds = Site::where('client_id', $clientId)->pluck('id');
        $categories = PortfolioCategory::whereIn('site_id', $siteIds)->orderBy('name')->get();
        $hasPosts = Post::whereIn('site_id', $siteIds)->whereHas('media')->exists();
        $hasLibrary = Media::where('model_type', Client::class)->where('model_id', $clientId)->exists();

        $filters = [['key' => 'all', 'label' => 'Todas', 'count' => null]];

        foreach ($categories as $cat) {
            $itemIds = PortfolioItem::where('portfolio_category_id', $cat->id)->pluck('id');
            $count = Media::where('model_type', PortfolioItem::class)->whereIn('model_id', $itemIds)->count();
            if ($count > 0) {
                $filters[] = ['key' => 'cat_' . $cat->id, 'label' => $cat->name, 'count' => $count];
            }
        }

        if ($hasPosts) {
            $postIds = Post::whereIn('site_id', $siteIds)->pluck('id');
            $count = Media::where('model_type', Post::class)->whereIn('model_id', $postIds)->count();
            $filters[] = ['key' => 'posts', 'label' => 'Posts', 'count' => $count];
        }

        if ($hasLibrary) {
            $count = Media::where('model_type', Client::class)->where('model_id', $clientId)->count();
            $filters[] = ['key' => 'library', 'label' => 'Subidas', 'count' => $count];
        }

        return $filters;
    }

    public function getMediaProperty()
    {
        $clientId = $this->getClientId();
        if (! $clientId) return Media::query()->whereRaw('1=0')->paginate($this->perPage);

        $siteIds = Site::where('client_id', $clientId)->pluck('id');

        $query = Media::query();

        if ($this->filter === 'all') {
            $postIds = Post::whereIn('site_id', $siteIds)->pluck('id');
            $portfolioCatIds = PortfolioCategory::whereIn('site_id', $siteIds)->pluck('id');
            $portfolioItemIds = PortfolioItem::whereIn('portfolio_category_id', $portfolioCatIds)->pluck('id');

            $query->where(function ($q) use ($clientId, $postIds, $portfolioCatIds, $portfolioItemIds) {
                $q->where(fn ($q2) => $q2->where('model_type', Client::class)->where('model_id', $clientId))
                    ->orWhere(fn ($q2) => $q2->where('model_type', Post::class)->whereIn('model_id', $postIds))
                    ->orWhere(fn ($q2) => $q2->where('model_type', PortfolioCategory::class)->whereIn('model_id', $portfolioCatIds))
                    ->orWhere(fn ($q2) => $q2->where('model_type', PortfolioItem::class)->whereIn('model_id', $portfolioItemIds));
            });
        } elseif (str_starts_with($this->filter, 'cat_')) {
            $catId = (int) str_replace('cat_', '', $this->filter);
            $itemIds = PortfolioItem::where('portfolio_category_id', $catId)->pluck('id');
            $query->where('model_type', PortfolioItem::class)->whereIn('model_id', $itemIds);
        } elseif ($this->filter === 'posts') {
            $postIds = Post::whereIn('site_id', $siteIds)->pluck('id');
            $query->where('model_type', Post::class)->whereIn('model_id', $postIds);
        } elseif ($this->filter === 'library') {
            $query->where('model_type', Client::class)->where('model_id', $clientId);
        }

        return $query
            ->when($this->search, fn ($q) => $q->where('file_name', 'like', "%{$this->search}%"))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
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

    public function updatedFilter(): void
    {
        $this->resetPage();
        $this->selectedMediaId = null;
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public static function getMediaUrl(Media $media): string
    {
        return $media->getCustomProperty('original_url') ?? $media->getUrl();
    }
}

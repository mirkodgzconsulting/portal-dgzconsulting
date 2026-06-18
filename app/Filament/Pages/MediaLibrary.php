<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Post;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\Site;
use Filament\Pages\Page;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibrary extends Page
{
    use WithPagination;

    protected static \BackedEnum|string|null $navigationIcon = 'phosphor-image-light';

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Contenido';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Media::count();
    }

    protected string $view = 'filament.pages.media-library';

    public string $search = '';
    public string $viewMode = 'grid';
    public string $filterClient = 'all';
    public string $filterType = 'all';
    public int $perPage = 48;
    public ?int $selectedMediaId = null;

    public function getClientsWithMedia(): array
    {
        $clientIds = Media::where('model_type', Client::class)
            ->pluck('model_id')
            ->unique();

        $postClientIds = Site::whereIn('id',
            Post::whereIn('id', Media::where('model_type', Post::class)->pluck('model_id'))->pluck('site_id')
        )->pluck('client_id');

        $portfolioClientIds = Site::whereIn('id',
            PortfolioCategory::whereIn('id',
                PortfolioItem::whereIn('id', Media::where('model_type', PortfolioItem::class)->pluck('model_id'))->pluck('portfolio_category_id')
            )->pluck('site_id')
        )->pluck('client_id');

        $allClientIds = $clientIds->merge($postClientIds)->merge($portfolioClientIds)->unique();

        return Client::whereIn('id', $allClientIds)->orderBy('name')->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    public function getMediaProperty()
    {
        $query = Media::query();

        if ($this->filterClient !== 'all') {
            $clientId = (int) $this->filterClient;
            $siteIds = Site::where('client_id', $clientId)->pluck('id');
            $postIds = Post::whereIn('site_id', $siteIds)->pluck('id');
            $portfolioCatIds = PortfolioCategory::whereIn('site_id', $siteIds)->pluck('id');
            $portfolioItemIds = PortfolioItem::whereIn('portfolio_category_id', $portfolioCatIds)->pluck('id');

            $query->where(function ($q) use ($clientId, $postIds, $portfolioCatIds, $portfolioItemIds) {
                $q->where(fn ($q2) => $q2->where('model_type', Client::class)->where('model_id', $clientId))
                    ->orWhere(fn ($q2) => $q2->where('model_type', Post::class)->whereIn('model_id', $postIds))
                    ->orWhere(fn ($q2) => $q2->where('model_type', PortfolioCategory::class)->whereIn('model_id', $portfolioCatIds))
                    ->orWhere(fn ($q2) => $q2->where('model_type', PortfolioItem::class)->whereIn('model_id', $portfolioItemIds));
            });
        }

        if ($this->filterType !== 'all') {
            $typeMap = [
                'posts' => Post::class,
                'portfolio' => PortfolioItem::class,
                'library' => Client::class,
            ];
            if (isset($typeMap[$this->filterType])) {
                $query->where('model_type', $typeMap[$this->filterType]);
            }
        }

        return $query
            ->when($this->search, fn ($q) => $q->where('file_name', 'like', "%{$this->search}%"))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function getSelectedMedia(): ?Media
    {
        if (! $this->selectedMediaId) return null;
        return Media::with('model')->find($this->selectedMediaId);
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

    public function toggleView(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClient(): void
    {
        $this->resetPage();
        $this->selectedMediaId = null;
    }

    public function updatedFilterType(): void
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

    public function getOwnerName(Media $media): string
    {
        if ($media->model_type === Client::class) {
            return $media->model?->name ?? 'Cliente #' . $media->model_id;
        }
        if ($media->model_type === Post::class) {
            $post = $media->model;
            return $post?->site?->client?->name ?? 'Post #' . $media->model_id;
        }
        if ($media->model_type === PortfolioItem::class) {
            $item = $media->model;
            $cat = $item?->portfolioCategory ?? PortfolioCategory::find($item?->portfolio_category_id);
            return $cat?->site?->client?->name ?? 'Portfolio';
        }
        if ($media->model_type === PortfolioCategory::class) {
            return $media->model?->site?->client?->name ?? 'Portfolio';
        }
        return 'Desconocido';
    }

    public function getOwnerContext(Media $media): string
    {
        $type = class_basename($media->model_type);
        if ($type === 'Post') return 'Post: ' . ($media->model?->title ?? '#' . $media->model_id);
        if ($type === 'PortfolioItem') {
            $cat = PortfolioCategory::find($media->model?->portfolio_category_id);
            return 'Portfolio: ' . ($cat?->name ?? '');
        }
        if ($type === 'PortfolioCategory') return 'Portfolio cover';
        if ($type === 'Client') return 'Library';
        return $type;
    }
}

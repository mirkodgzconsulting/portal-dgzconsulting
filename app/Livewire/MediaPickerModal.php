<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Post;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\Site;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPickerModal extends Component
{
    use WithFileUploads;

    public string $search = '';
    public ?int $selectedId = null;
    public $uploadFile = null;
    public string $activeTab = 'library';

    public function getClientId(): ?int
    {
        return Auth::guard('client')->id()
            ?? Auth::guard('client_user')->user()?->client_id;
    }

    public function getMediaProperty()
    {
        $clientId = $this->getClientId();
        if (! $clientId) return collect();

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
            ->limit(30)
            ->get();
    }

    public function selectMedia(int $id): void
    {
        $this->selectedId = $id;
    }

    public function confirmSelection(): void
    {
        if ($this->selectedId) {
            $media = Media::find($this->selectedId);
            if ($media) {
                $this->dispatch('media-selected', url: $media->getUrl(), mediaId: $media->id);
            }
        }
    }

    public function updatedUploadFile(): void
    {
        $clientId = $this->getClientId();
        $client = Client::find($clientId);
        if (! $client || ! $this->uploadFile) return;

        $media = $client->addMedia($this->uploadFile->getRealPath())
            ->usingFileName($this->uploadFile->getClientOriginalName())
            ->toMediaCollection('library', 'r2');

        $this->uploadFile = null;
        $this->selectedId = $media->id;
        $this->activeTab = 'library';
    }

    public function render()
    {
        return view('livewire.media-picker-modal', [
            'media' => $this->getMediaProperty(),
        ]);
    }
}

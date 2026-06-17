<?php

namespace App\Console\Commands;

use App\Models\PortfolioItem;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMediaToCurator extends Command
{
    protected $signature = 'media:sync-curator';
    protected $description = 'Register R2 images (portfolio items + post covers) in Curator media table';

    public function handle(): int
    {
        $synced = 0;

        $this->info('Syncing portfolio items...');
        $items = PortfolioItem::with('category.site')->get();

        foreach ($items as $item) {
            if (! $item->image_url || ! str_starts_with($item->image_url, 'http')) continue;
            if ($this->alreadyExists($item->image_url)) continue;

            $clientId = $item->category?->site?->client_id;
            $this->insertMedia($item->image_url, $item->title, $clientId);
            $synced++;
        }
        $this->info("  → {$synced} portfolio images synced");

        $postsSynced = 0;
        $this->info('Syncing post cover images...');
        $posts = Post::with('site')->whereNotNull('cover_image')->get();

        foreach ($posts as $post) {
            if (! str_starts_with($post->cover_image, 'http')) continue;
            if ($this->alreadyExists($post->cover_image)) continue;

            $clientId = $post->site?->client_id;
            $this->insertMedia($post->cover_image, $post->title, $clientId);
            $postsSynced++;
        }
        $this->info("  → {$postsSynced} post covers synced");

        $this->newLine();
        $this->info("Done! Total synced: " . ($synced + $postsSynced));

        return 0;
    }

    private function alreadyExists(string $url): bool
    {
        return DB::table('curator')->where('path', $url)->exists();
    }

    private function insertMedia(string $url, ?string $title, ?int $clientId): void
    {
        $parsed = parse_url($url);
        $path = ltrim($parsed['path'] ?? '', '/');
        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        $name = pathinfo($path, PATHINFO_FILENAME);

        DB::table('curator')->insert([
            'disk' => 'r2',
            'directory' => dirname($path),
            'visibility' => 'public',
            'name' => $name,
            'path' => $url,
            'width' => null,
            'height' => null,
            'size' => null,
            'type' => 'image',
            'ext' => $ext,
            'alt' => $title,
            'title' => $title,
            'description' => null,
            'caption' => null,
            'pretty_name' => $title ?? $name,
            'exif' => null,
            'curations' => null,
            'tenant_id' => null,
            'client_id' => $clientId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

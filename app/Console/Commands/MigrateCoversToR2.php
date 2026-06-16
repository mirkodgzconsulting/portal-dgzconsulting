<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MigrateCoversToR2 extends Command
{
    protected $signature = 'posts:migrate-covers-to-r2
        {site_slug : Slug del Site en el CRM (p.ej. modelo-octatrico)}';

    protected $description = 'Descarga las cover_image que sean URLs externas y las sube a R2, actualizando el Post a la ruta relativa';

    public function handle(): int
    {
        $site = Site::where('slug', $this->argument('site_slug'))->first();

        if (! $site) {
            $this->error("No existe ningún Site con slug \"{$this->argument('site_slug')}\".");

            return self::FAILURE;
        }

        $r2Url = rtrim((string) config('filesystems.disks.r2.url'), '/');

        $posts = $site->posts()
            ->whereNotNull('cover_image')
            ->where('cover_image', 'like', 'http%')
            ->get();

        $migrated = 0;
        $failed = 0;

        foreach ($posts as $post) {
            $url = $post->cover_image;

            if ($r2Url !== '' && str_starts_with($url, $r2Url)) {
                continue;
            }

            $response = Http::get($url);

            if (! $response->successful()) {
                $this->warn("  [{$post->slug}] HTTP {$response->status()} al descargar {$url}");
                $failed++;

                continue;
            }

            $filename = $this->resolveFilename($url, $response->header('Content-Type'));
            $path = "posts/{$site->slug}/{$filename}";

            Storage::disk('r2')->put($path, $response->body());

            $post->update(['cover_image' => $path]);

            $this->info("  [{$post->slug}] -> {$path}");
            $migrated++;
        }

        $this->info("Migradas {$migrated} portadas a R2 ({$failed} fallidas) para \"{$site->name}\".");

        return self::SUCCESS;
    }

    private function resolveFilename(string $url, ?string $contentType): string
    {
        $basename = basename(parse_url($url, PHP_URL_PATH) ?? '');
        $basename = preg_replace('/[^A-Za-z0-9._-]/', '_', $basename) ?: 'cover';

        if (! pathinfo($basename, PATHINFO_EXTENSION)) {
            $basename .= '.'.match (true) {
                str_contains((string) $contentType, 'png') => 'png',
                str_contains((string) $contentType, 'webp') => 'webp',
                str_contains((string) $contentType, 'gif') => 'gif',
                default => 'jpg',
            };
        }

        return $basename;
    }
}

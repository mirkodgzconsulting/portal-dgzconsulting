<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportStrapiPosts extends Command
{
    protected $signature = 'posts:import-from-strapi
        {site_slug : Slug del Site en el CRM (p.ej. modelo-octatrico)}
        {strapi_url : URL base de Strapi (p.ej. https://cms.dgzconsulting.com)}
        {strapi_token : Token de API de Strapi}
        {strapi_site_slug : Slug del sitio en Strapi (filters[site][slug][$eq])}
        {--fresh : Borra los posts existentes de este site antes de importar}';

    protected $description = 'Importa los posts de un sitio desde Strapi al módulo Post del CRM';

    public function handle(): int
    {
        $site = Site::where('slug', $this->argument('site_slug'))->first();

        if (! $site) {
            $this->error("No existe ningún Site con slug \"{$this->argument('site_slug')}\".");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $site->posts()->delete();
            $this->warn('Posts existentes de este site eliminados (--fresh).');
        }

        $strapiUrl = rtrim($this->argument('strapi_url'), '/');
        $strapiToken = $this->argument('strapi_token');
        $strapiSiteSlug = $this->argument('strapi_site_slug');

        $response = Http::withToken($strapiToken)
            ->get("{$strapiUrl}/api/posts", [
                'filters[site][slug][$eq]' => $strapiSiteSlug,
                'populate' => '*',
            ]);

        if (! $response->successful()) {
            $this->error("Error al consultar Strapi (HTTP {$response->status()}).");

            return self::FAILURE;
        }

        $posts = $response->json('data', []);
        $count = 0;

        foreach ($posts as $post) {
            $slug = $post['slug'] ?? Str::slug($post['title'] ?? '');

            if ($slug === '') {
                continue;
            }

            Post::updateOrCreate(
                ['site_id' => $site->id, 'slug' => $slug],
                [
                    'title' => $post['title'] ?? $slug,
                    'description' => $post['description'] ?? '',
                    'content' => $post['content'] ?? '',
                    'cover_image' => $this->resolveCoverImage($strapiUrl, $post['cover_image'] ?? null),
                    'tags' => $this->parseTags($post['tags'] ?? null),
                    'author' => $post['author'] ?? null,
                    'pub_date' => $post['pubDate'] ?? now(),
                    'published' => true,
                ]
            );
            $count++;
        }

        $this->info("Importados {$count} posts para el site \"{$site->name}\" (slug: {$site->slug}).");

        return self::SUCCESS;
    }

    private function resolveCoverImage(string $strapiUrl, ?array $media): ?string
    {
        $url = $media['url'] ?? null;

        if (! $url) {
            return null;
        }

        return str_starts_with($url, 'http') ? $url : $strapiUrl.$url;
    }

    /**
     * @return array<int, string>
     */
    private function parseTags(?string $tags): array
    {
        if (! $tags) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }
}

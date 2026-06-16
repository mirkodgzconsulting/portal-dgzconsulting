<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Site;
use Illuminate\Console\Command;

class ImportMarkdownPosts extends Command
{
    protected $signature = 'posts:import-from-markdown
        {site_slug : Slug del Site en el CRM (p.ej. conkretperu)}
        {path : Carpeta absoluta con los archivos .md}
        {--base-url= : URL base para resolver imágenes relativas (p.ej. https://conkretperu.com)}
        {--fresh : Borra los posts existentes de este site antes de importar}';

    protected $description = 'Importa posts desde archivos markdown con frontmatter al módulo Post del CRM';

    public function handle(): int
    {
        $site = Site::where('slug', $this->argument('site_slug'))->first();

        if (! $site) {
            $this->error("No existe ningún Site con slug \"{$this->argument('site_slug')}\".");

            return self::FAILURE;
        }

        $path = rtrim($this->argument('path'), '/');

        if (! is_dir($path)) {
            $this->error("No existe el directorio \"{$path}\".");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $site->posts()->delete();
            $this->warn('Posts existentes de este site eliminados (--fresh).');
        }

        $baseUrl = $this->option('base-url') ? rtrim($this->option('base-url'), '/') : null;

        $files = glob("{$path}/*.md") ?: [];
        $count = 0;

        foreach ($files as $file) {
            $slug = basename($file, '.md');
            $raw = file_get_contents($file);

            ['frontmatter' => $frontmatter, 'body' => $body] = $this->parseFrontmatter($raw);

            $isDraft = (bool) ($frontmatter['isDraft'] ?? false);

            Post::updateOrCreate(
                ['site_id' => $site->id, 'slug' => $slug],
                [
                    'title' => $frontmatter['title'] ?? $slug,
                    'description' => $frontmatter['description'] ?? '',
                    'content' => $body,
                    'cover_image' => $this->resolveCoverImage($baseUrl, $frontmatter['image'] ?? null),
                    'tags' => $frontmatter['tags'] ?? [],
                    'author' => $frontmatter['author'] ?? 'CONKRET',
                    'pub_date' => $frontmatter['pubDate'] ?? now(),
                    'published' => ! $isDraft,
                    'featured' => (bool) ($frontmatter['featured'] ?? false),
                ]
            );
            $count++;
        }

        $this->info("Importados {$count} posts para el site \"{$site->name}\" (slug: {$site->slug}).");

        return self::SUCCESS;
    }

    private function resolveCoverImage(?string $baseUrl, ?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (str_starts_with($image, 'http')) {
            return $image;
        }

        if ($baseUrl === null) {
            return $image;
        }

        return $baseUrl.'/'.ltrim($image, '/');
    }

    /**
     * Separa el frontmatter (entre delimitadores "---") del cuerpo markdown,
     * y parsea el frontmatter con un parser propio (formato simple:
     * key: value, soporta strings entre comillas, fechas y booleanos sueltos,
     * y arrays de strings entre comillas en una sola línea).
     *
     * @return array{frontmatter: array<string, mixed>, body: string}
     */
    private function parseFrontmatter(string $content): array
    {
        $content = ltrim(str_replace("\r\n", "\n", $content));

        if (! str_starts_with($content, '---')) {
            return ['frontmatter' => [], 'body' => $content];
        }

        $parts = preg_split('/\n---\s*\n/', substr($content, 3), 2);

        if (count($parts) < 2) {
            return ['frontmatter' => [], 'body' => $content];
        }

        [$rawFrontmatter, $body] = $parts;

        $frontmatter = [];

        foreach (explode("\n", trim($rawFrontmatter)) as $line) {
            if (trim($line) === '' || ! preg_match('/^(\w+):\s*(.*)$/', $line, $m)) {
                continue;
            }

            $frontmatter[$m[1]] = $this->parseScalar(trim($m[2]));
        }

        return ['frontmatter' => $frontmatter, 'body' => ltrim($body, "\n")];
    }

    private function parseScalar(string $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/^"(.*)"$/', $value, $m)) {
            return $m[1];
        }

        if (preg_match('/^\[(.*)\]$/', $value, $m)) {
            $items = [];

            foreach (preg_split('/,\s*/', trim($m[1])) ?: [] as $item) {
                $item = trim($item);

                if (preg_match('/^"(.*)"$/', $item, $im)) {
                    $items[] = $im[1];
                } elseif ($item !== '') {
                    $items[] = $item;
                }
            }

            return $items;
        }

        if (in_array($value, ['true', 'false'], true)) {
            return $value === 'true';
        }

        return $value;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportPabloPinxitPortfolio extends Command
{
    protected $signature = 'portfolio:import-pablo';
    protected $description = 'Import Pablo Pinxit images from Astro public folder to R2 and create PortfolioItems';

    private string $basePath = '/Users/mirkodgz/Projects/Pablo Pinxit/pablopinxit-sito/public';

    private array $folderMap = [
        'walls' => 'walls',
        'shutters' => 'Shutters',
        'dadapop' => 'Dadapop',
        'ikons' => 'Ikons',
        'hybris' => 'Hybris',
        'mind-blowing-garden' => 'Mind Blowing Garden',
    ];

    public function handle(): int
    {
        $categories = PortfolioCategory::where('site_id', 3)->get()->keyBy('slug');

        if ($categories->isEmpty()) {
            $this->error('No portfolio categories found for site_id 3');
            return 1;
        }

        $totalUploaded = 0;

        foreach ($this->folderMap as $slug => $folder) {
            $category = $categories->get($slug);
            if (! $category) {
                $this->warn("Category '{$slug}' not found, skipping");
                continue;
            }

            $existingCount = $category->items()->count();
            if ($existingCount > 0) {
                $this->info("'{$category->name}' already has {$existingCount} items, skipping");
                continue;
            }

            $folderPath = $this->basePath . '/' . $folder;
            $images = $this->findImages($folderPath);

            $this->info("Uploading {$images->count()} images for '{$category->name}'...");

            $bar = $this->output->createProgressBar($images->count());
            $sortOrder = 0;

            foreach ($images as $imagePath) {
                $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                $originalName = pathinfo($imagePath, PATHINFO_FILENAME);
                $filename = Str::uuid() . '.' . $ext;
                $r2Path = "portfolio/pablopinxit/{$slug}/{$filename}";

                Storage::disk('r2')->put($r2Path, file_get_contents($imagePath), 'public');
                $url = Storage::disk('r2')->url($r2Path);

                PortfolioItem::create([
                    'portfolio_category_id' => $category->id,
                    'title' => Str::title(str_replace(['-', '_'], ' ', $originalName)),
                    'image_url' => $url,
                    'sort_order' => $sortOrder++,
                ]);

                $totalUploaded++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            if ($images->isNotEmpty() && ! $category->cover_image) {
                $firstItem = $category->items()->orderBy('sort_order')->first();
                $category->update(['cover_image' => $firstItem->image_url]);
                $this->info("  → Cover image set from first item");
            }
        }

        $this->newLine();
        $this->info("Done! {$totalUploaded} images uploaded to R2.");

        return 0;
    }

    private function findImages(string $path): \Illuminate\Support\Collection
    {
        if (! is_dir($path)) {
            return collect();
        }

        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        return collect(scandir($path))
            ->filter(function ($file) use ($path, $extensions) {
                if ($file === '.' || $file === '..') return false;
                $fullPath = $path . '/' . $file;

                if (is_dir($fullPath)) {
                    return false;
                }

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                return in_array($ext, $extensions);
            })
            ->map(fn ($file) => $path . '/' . $file)
            ->merge($this->findImagesInSubdirs($path, $extensions))
            ->sort()
            ->values();
    }

    private function findImagesInSubdirs(string $path, array $extensions): \Illuminate\Support\Collection
    {
        $images = collect();

        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $fullPath = $path . '/' . $entry;

            if (is_dir($fullPath)) {
                $images = $images->merge(
                    collect(scandir($fullPath))
                        ->filter(function ($file) use ($fullPath, $extensions) {
                            if ($file === '.' || $file === '..') return false;
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            return in_array($ext, $extensions) && is_file($fullPath . '/' . $file);
                        })
                        ->map(fn ($file) => $fullPath . '/' . $file)
                );
            }
        }

        return $images;
    }
}

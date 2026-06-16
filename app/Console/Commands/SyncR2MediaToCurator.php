<?php

namespace App\Console\Commands;

use Awcodes\Curator\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncR2MediaToCurator extends Command
{
    protected $signature = 'curator:sync-r2
                            {--dry-run : Mostrar qué se importaría sin guardar nada}
                            {--force : Re-importar archivos ya registrados}';

    protected $description = 'Importa todas las imágenes existentes en R2 a la biblioteca de Curator';

    private array $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'];

    private array $imageMimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'avif' => 'image/avif',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');

        $this->info('Conectando con R2 y listando archivos...');

        try {
            $allFiles = Storage::disk('r2')->allFiles();
        } catch (\Throwable $e) {
            $this->error('Error al conectar con R2: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Filtrar solo imágenes
        $imageFiles = array_filter($allFiles, function (string $path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            return in_array($ext, $this->imageExtensions);
        });

        $imageFiles = array_values($imageFiles);
        $total      = count($imageFiles);

        if ($total === 0) {
            $this->warn('No se encontraron imágenes en el bucket R2.');
            return self::SUCCESS;
        }

        $this->info("Se encontraron {$total} imágenes en R2.");

        if ($dryRun) {
            $this->table(['Ruta'], array_map(fn ($f) => [$f], $imageFiles));
            $this->warn('[dry-run] Nada fue guardado.');
            return self::SUCCESS;
        }

        // Paths ya registrados en Curator
        $existingPaths = Media::where('disk', 'r2')->pluck('path')->flip();

        $imported = 0;
        $skipped  = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($imageFiles as $path) {
            $bar->advance();

            if (!$force && $existingPaths->has($path)) {
                $skipped++;
                continue;
            }

            try {
                $ext       = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $name      = pathinfo($path, PATHINFO_FILENAME);
                $directory = pathinfo($path, PATHINFO_DIRNAME);
                $directory = $directory === '.' ? null : $directory;
                $mime      = $this->imageMimes[$ext] ?? 'image/' . $ext;

                $size = null;
                try {
                    $size = Storage::disk('r2')->size($path);
                } catch (\Throwable) {
                    // tamaño no disponible — continuar igual
                }

                $data = [
                    'disk'       => 'r2',
                    'directory'  => $directory,
                    'visibility' => 'public',
                    'name'       => $name . '.' . $ext,
                    'path'       => $path,
                    'size'       => $size,
                    'type'       => $mime,
                    'ext'        => $ext,
                    'alt'        => Str::headline($name),
                    'pretty_name' => Str::headline($name),
                    'width'      => null,
                    'height'     => null,
                ];

                if ($force && $existingPaths->has($path)) {
                    Media::where('disk', 'r2')->where('path', $path)->update($data);
                } else {
                    Media::create($data);
                }

                $imported++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("  Error en {$path}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Importadas: {$imported}");
        if ($skipped > 0) {
            $this->line("  Omitidas (ya existían): {$skipped} — usa --force para re-importarlas.");
        }

        return self::SUCCESS;
    }
}

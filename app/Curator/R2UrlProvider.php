<?php

namespace App\Curator;

use Awcodes\Curator\Concerns\UrlProvider;
use Illuminate\Support\Facades\Storage;

/**
 * Genera URLs directas del bucket R2 para las previsualizaciones de Curator.
 * Evita pasar por Glide (que solo funciona con disco local).
 */
class R2UrlProvider implements UrlProvider
{
    public static function getThumbnailUrl(string $path): string
    {
        return Storage::disk('r2')->url($path);
    }

    public static function getMediumUrl(string $path): string
    {
        return Storage::disk('r2')->url($path);
    }

    public static function getLargeUrl(string $path): string
    {
        return Storage::disk('r2')->url($path);
    }
}

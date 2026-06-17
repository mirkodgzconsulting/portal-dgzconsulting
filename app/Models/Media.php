<?php

namespace App\Models;

use App\Observers\MediaObserver;
use Awcodes\Curator\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[ObservedBy(MediaObserver::class)]
class Media extends BaseMedia
{
    protected static function booted(): void
    {
        static::addGlobalScope('client_media', function (Builder $query) {
            $clientId = app()->bound('client_panel_client_id')
                ? app('client_panel_client_id')
                : null;

            if ($clientId) {
                $query->where('client_id', $clientId);
            }
        });
    }



    protected $fillable = [
        'disk',
        'directory',
        'visibility',
        'name',
        'path',
        'width',
        'height',
        'size',
        'type',
        'ext',
        'alt',
        'title',
        'description',
        'caption',
        'pretty_name',
        'exif',
        'curations',
        'tenant_id',
        'client_id',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

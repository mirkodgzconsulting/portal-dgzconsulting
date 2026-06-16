<?php

namespace App\Models;

use App\Observers\MediaObserver;
use Awcodes\Curator\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(MediaObserver::class)]
class Media extends BaseMedia
{
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

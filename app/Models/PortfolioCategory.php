<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PortfolioCategory extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $cat) {
            if (blank($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }
}

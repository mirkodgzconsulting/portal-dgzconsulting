<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nomanur\FilamentSeoPro\Traits\HasSeo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasSeo, InteractsWithMedia;
    protected $fillable = [
        'site_id',
        'category_id',
        'title',
        'slug',
        'description',
        'content',
        'cover_image',
        'tags',
        'author',
        'pub_date',
        'published',
        'featured',
    ];

    protected $casts = [
        'tags' => 'array',
        'pub_date' => 'date',
        'published' => 'boolean',
        'featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        $mediaUrl = $this->getFirstMediaUrl('cover');
        if ($mediaUrl) {
            return $mediaUrl;
        }

        if (! $this->cover_image) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        return Storage::disk('r2')->url($this->cover_image);
    }
}

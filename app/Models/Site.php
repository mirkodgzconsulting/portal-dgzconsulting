<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'domain',
        'slug',
        'admin_url',
        'cms_username',
        'cms_password',
        'cms_type',
        'hosting_provider',
        'has_blog',
        'notes',
    ];

    protected $casts = [
        'cms_username' => 'encrypted',
        'cms_password' => 'encrypted',
        'has_blog' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}

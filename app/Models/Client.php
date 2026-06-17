<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Client extends Model implements AuthenticatableContract, FilamentUser, HasName, HasMedia
{
    use Authenticatable, InteractsWithMedia, Notifiable;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('library');
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'secondary_email',
        'active',
        'gender',
        'logo',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'active' => 'boolean',
        'password' => 'hashed',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && $panel->getId() === 'cliente';
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}

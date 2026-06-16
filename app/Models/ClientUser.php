<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class ClientUser extends Model implements AuthenticatableContract, FilamentUser, HasName
{
    use Authenticatable, Notifiable;

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'active' => 'boolean',
        'password' => 'hashed',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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

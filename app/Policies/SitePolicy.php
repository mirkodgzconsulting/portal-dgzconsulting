<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Site;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class SitePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        if ($user instanceof Client) {
            return true;
        }

        return $user->isAdmin();
    }

    public function view(Authenticatable $user, Site $site): bool
    {
        if ($user instanceof Client) {
            return $site->client_id === $user->id;
        }

        return $user->isAdmin();
    }

    public function create(Authenticatable $user): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        return $user->isAdmin();
    }

    public function update(Authenticatable $user, Site $site): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        return $user->isAdmin();
    }

    public function delete(Authenticatable $user, Site $site): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        return $user->isAdmin();
    }

    public function restore(Authenticatable $user, Site $site): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public function forceDelete(Authenticatable $user, Site $site): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public function viewCmsCredentials(Authenticatable $user, Site $site): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        return $user->isSuperAdmin();
    }
}

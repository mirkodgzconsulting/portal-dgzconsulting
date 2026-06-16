<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Awcodes\Curator\Models\Media::class,
            \App\Models\Media::class,
        );
    }

    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Providers;

use BladeUI\Icons\Factory as IconFactory;
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
        $this->callAfterResolving(IconFactory::class, function (IconFactory $factory) {
            $factory->add('geist', [
                'path' => resource_path('svg/geist'),
                'prefix' => 'geist',
            ]);
        });
    }
}

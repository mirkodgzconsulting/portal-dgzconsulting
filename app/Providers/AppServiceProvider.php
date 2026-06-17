<?php

namespace App\Providers;

use App\Models\Post;
use BladeUI\Icons\Factory as IconFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

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

        Event::listen(MediaHasBeenAddedEvent::class, function (MediaHasBeenAddedEvent $event) {
            $media = $event->media;
            if ($media->model_type === Post::class && $media->collection_name === 'cover') {
                $media->model->updateQuietly(['cover_image' => $media->getUrl()]);
            }
        });
    }
}

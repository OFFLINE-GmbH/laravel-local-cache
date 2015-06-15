<?php

namespace Offline\LocalCache;

use Illuminate\Support\ServiceProvider;
use Offline\LocalCache\ValueObjects\Ttl;

class OfflineCacheServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/localcache.php' => config_path('localcache.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['LocalCache'] = $this->app->share(function ($app) {
            $config = $app->config->get('localcache', [
                'base_url'     => $app['url']->to('/'),
                'storage_path' => storage_path('localcache'),
                'ttl'          => 20,
            ]);

            $ttl = new Ttl($config['ttl']);

            return new LocalCache($config['storage_path'], $ttl);
        });
    }
}

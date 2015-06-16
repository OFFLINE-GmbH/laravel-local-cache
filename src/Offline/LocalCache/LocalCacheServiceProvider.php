<?php

namespace Offline\LocalCache;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Offline\LocalCache\ValueObjects\Ttl;

class LocalCacheServiceProvider extends ServiceProvider
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

        $this->defineRoutes();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['LocalCache'] = $this->app->share(function ($app) {

            $config = $app->config->get('localcache', [
                'base_url'     => $app['url']->to('/'),
                'route'        => 'cache',
                'storage_path' => storage_path('localcache'),
                'ttl'          => 20,
            ]);

            $ttl = new Ttl($config['ttl']);

            return new LocalCache($config['storage_path'], $config['base_url'] . '/' . $config['route'], $ttl);
        });
    }

    private function defineRoutes()
    {
        if ( ! $this->app->routesAreCached()) {

            $route   = $this->app->config->get('localcache.route', 'cache');
            $storage = $this->app->config->get('localcache.storage_path', storage_path('localcache'));

            Route::get('/' . $route . '/{hash}', function ($hash) use ($storage) {
                return (new Response(file_get_contents($storage . '/' . $hash), 200))->header('Content-Type', 'text/css');
            });

        }
    }
}

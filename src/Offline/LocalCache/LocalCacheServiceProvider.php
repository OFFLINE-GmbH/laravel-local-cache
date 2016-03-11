<?php

namespace Offline\LocalCache;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Offline\LocalCache\ValueObjects\Ttl;

/**
 * Class LocalCacheServiceProvider
 * @package Offline\LocalCache
 */
class LocalCacheServiceProvider extends ServiceProvider
{
    /**
     * @var MimeMap
     */
    protected $mimeMap;

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
                'route'         => 'cache',
                'base_url'      => $app['url']->to('/'),
                'storage_path'  => storage_path('localcache'),
                'ttl'           => 3600,
                'max_file_size' => 1310720,
            ]);

            $ttl = new Ttl($config['ttl']);

            return new LocalCache(
                $config['storage_path'],
                $config['base_url'] . '/' . $config['route'],
                $ttl,
                $config['max_file_size']
            );

        });
    }

    /**
     * Routes
     */
    private function defineRoutes()
    {
        if ( ! $this->app->routesAreCached()) {
            $route   = $this->app->config->get('localcache.route', 'cache');
            $storage = $this->app['LocalCache']->getCachePath();


            $this->mimeMap = MimeMap::readMapFile($storage . '/mimeMap.json');
            Route::get('/' . $route . '/{hash}', function ($hash) use ($storage) {

                $filePath = $storage . '/' . $hash;
                if ( ! file_exists($filePath)) {
                    return new Response("$filePath not found.", 404);
                }

                return (
                new Response(file_get_contents($filePath), 200))
                    ->header('Content-Type', $this->getMime($hash));
            });
        }
    }

    /**
     * Return the file's mime type.
     * 
     * @param $hash
     *
     * @return string
     */
    private function getMime($hash)
    {
        return is_array($this->mimeMap) && array_key_exists($hash, $this->mimeMap)
            ? $this->mimeMap[$hash]
            : 'application/octet-stream';
    }
}

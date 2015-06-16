# Local File Cache for Laravel 5
[![Build Status](https://travis-ci.org/OFFLINE-GmbH/laravel-local-cache.svg)](https://travis-ci
.org/OFFLINE-GmbH/laravel-local-cache)

This package allows you to cache remote files in the local filesystem. This is useful if you try
to reduce bandwidth consumption when requesting files from services like Amazon S3. 

The implementation also enables you to serve files from cache that may not be available at
their remote location at times.


## Install it
To install this package include it in your `composer.json` and run `composer update`:

    "require": {
       "offline/laravel-local-cache": "~1.0"
    }
    
     
Add the Service Provider to the `provider` array in your `config/app.php`

    'Offline\LocalCache\LocalCacheServiceProvider'
    
Add an alias for the facade to your `config/app.php`

    'LocalCache' => 'Offline\LocalCache\Facades\LocalCache',

Publish the config:

    $ php artisan vendor:publish --provider="Offline\LocalCache\LocalCacheServiceProvider"
    
Create the directory `storage/localcache` (edit the `storage_path` setting in `config/localcache.php` to change 
this location).

## Use it

To cache a file use the `getCachedHtml` method. The file will be downloaded and stored to disk.
The method returns the local URL for your file.
    
    $string = 'http://www.offlinegmbh.ch/file.jpg';
    
    // returns http://yoursite/cache/{hash}
    var_dump(LocalCache::getCachedHtml($string));
    
By default, a `/cache/{hash}` route is generated which serves the file's contents with the correct mime type.
To change the route, edit the `route` setting in `config/localcache.php`.

The `getCachedHtml` method works with any string that contains any number of URLs. It extracts and replaces the links 
accordingly.

    $string = '<p>http://www.offlinegmbh.ch/file.jpg</p><p>http://www.offlinegmbh.ch/file2.jpg</p>';
    
    // <p>http://yoursite/cache/{hash1}</p><p>http://yoursite/cache/{hash2}</p>
    var_dump(LocalCache::getCachedHtml($string));
    
### Example Middleware

This example middleware caches all external files referenced in your template and replaces 
the URLs.

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    use Offline\LocalCache\Facades\LocalCache;
    
    class LocalCacheMiddleware
    {
    
        /**
         * Cache and replace links to external assets.
         *
         * @param  \Illuminate\Http\Request $request
         * @param  \Closure                 $next
         *
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            $response = $next($request);
    
            $response->setContent(
                LocalCache::getCachedHtml($response->getContent())
            );
    
            return $response;
        }
    
    }

 Add it to your `app/Http/Kernel.php`
 
 
    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'localcache'   => \App\Http\Middleware\LocalCacheMiddleware::class,
    ];
    
And use it in your `routes.php` or controller.

    Route::get('/', [
        'uses'       => 'PageController@index',
        'middleware' => ['localcache']
    ]);
 
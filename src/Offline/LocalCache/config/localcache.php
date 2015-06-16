<?php
return [
    'base_url'     => base_url(),
    'route'        => 'cache', // base_url/cache/{hash}
    'storage_path' => storage_path('localcache'),
    'ttl'          => 20, // Time to life in seconds
];
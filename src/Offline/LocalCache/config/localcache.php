<?php
return [
    // Base URL used for replacements.
    'base_url'     => base_url(),
    // Route path to serve cached files
    'route'        => 'cache', // base_url/cache/{hash}
    // Where to store the cached files
    'storage_path' => storage_path('localcache'),
    // Time to live for cached files
    'ttl'          => 20,
];
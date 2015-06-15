<?php namespace Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Ttl;
use Offline\LocalCache\ValueObjects\Url;

/**
 * Class LocalCache
 * @package Offline\LocalCache
 */
class LocalCache
{
    /**
     * Path to cache directory
     *
     * @var array
     */
    protected $cachePath;
    /**
     * Default TTL
     *
     * @var Ttl
     */
    protected $ttl;

    /**
     * URLs
     *
     * @var array
     */
    protected $urls = [];


    /**
     * Constructor
     */
    public function __construct($cachePath, Ttl $ttl)
    {
        // if( ! is_dir($cachePath) || ! is_writable($cachePath)) {
        //     throw new InvalidArgumentException("${cachePath} is not writeable!");
        // }
        $this->cachePath = $cachePath;
        $this->ttl       = $ttl;
    }


    /**
     * Extracts URLs from a string
     *
     * @param $html
     *
     * @return array
     */
    public function extractUrls($html)
    {
        preg_match_all('/https?\:\/\/[^\"\<]+/i', $html, $matches);

        foreach ($matches[0] as $url) {
            $this->urls[] = new CacheObject(new Url($url), $this->ttl, $this->cachePath);
        }

        return $matches[0];
    }
}

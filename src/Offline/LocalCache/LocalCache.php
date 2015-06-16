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
     * Path to cache directory
     *
     * @var array
     */
    protected $baseUrl;
    /**
     * Default TTL
     *
     * @var Ttl
     */
    protected $ttl;
    /**
     * CacheObjects
     *
     * @var array
     */
    protected $cacheObjects = [];


    /**
     * Constructor
     */
    public function __construct($cachePath, $baseUrl, Ttl $ttl)
    {
        if ( ! is_dir($cachePath) || ! is_writable($cachePath)) {
            throw new InvalidArgumentException("${cachePath} is not writeable!");
        }
        $this->cachePath = $cachePath;
        $this->ttl       = $ttl;
        $this->baseUrl   = $baseUrl;
    }

    /**
     * Adds a url to the cache.
     *
     * @param string $url
     */
    public function addUrl($url)
    {
        $this->cacheObjects[$url] = new CacheObject(new Url($url), $this->ttl, $this->cachePath);
    }

    /**
     * Returns a url to a cached file.
     *
     * @param $url
     *
     * @return string
     */
    public function getUrl($url)
    {
        if ( ! array_key_exists($url, $this->cacheObjects)) {
            return $url;
        }

        return (string)$this->baseUrl . '/' . $this->cacheObjects[$url];
    }

    /**
     * Replaces URLs in a string.
     *
     * @param $html
     *
     * @return array
     */
    public function getCachedHtml($html)
    {
        $this->extractUrls($html);

        $html = $this->replaceUrls($html);

        return $html;
    }

    public function flush()
    {
        foreach ($this->cacheObjects as $object) {
            $object->remove();
        }
    }

    /**
     * Extracts URLs from a string
     *
     * @param $html
     *
     * @return array
     */
    private function extractUrls($html)
    {
        preg_match_all('/https?\:\/\/[^\"\<]+/i', $html, $matches);

        foreach ($matches[0] as $url) {
            $this->addUrl($url);
        }
        return $this->cacheObjects;
    }


    private function replaceUrls($html)
    {
        preg_match_all('/https?\:\/\/[^\"\<]+/i', $html, $matches);
        foreach ($matches[0] as $url) {
            $html = str_replace($url, $this->getUrl($url), $html);
        }

        return $html;
    }

}

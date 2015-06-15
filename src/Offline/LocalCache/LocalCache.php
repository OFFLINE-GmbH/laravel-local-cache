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
        // if( ! is_dir($cachePath) || ! is_writable($cachePath)) {
        //     throw new InvalidArgumentException("${cachePath} is not writeable!");
        // }
        $this->cachePath = $cachePath;
        $this->ttl       = $ttl;
        $this->baseUrl   = $baseUrl;
    }

    public function addUrl($url)
    {
        $this->cacheObjects[$url] = new CacheObject(new Url($url), $this->ttl, $this->cachePath);
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
            $this->addUrl($url);
        }

        return $this->cacheObjects;
    }

    /**
     * Replaces URLs in a string
     *
     * @param $html
     *
     * @return array
     */
    public function getCachedHtml($html)
    {
        $this->extractUrls($html);

        $this->persist();

        $html = $this->replaceUrls($html);

        return $html;
    }

    private function replaceUrls($html)
    {
        preg_match_all('/https?\:\/\/[^\"\<]+/i', $html, $matches);
        foreach ($matches[0] as $url) {
            $html = str_replace($url, $this->getUrl($url), $html);
        }

        return $html;
    }

    /**
     * Persists all CacheObjects to disk.
     */
    public function persist()
    {
        foreach ($this->cacheObjects as $cacheObject) {
            $cacheObject->store();
        }
    }

    /**
     * Remove all CacheObjects from disk.
     */
    public function flush()
    {
        foreach ($this->cacheObjects as $cacheObject) {
            $cacheObject->remove();
        }
    }

    public function getUrl($url)
    {
        if ( ! array_key_exists($url, $this->cacheObjects)) {
            return $url;
        }

        return (string)$this->baseUrl . '/' . $this->cacheObjects[$url];
    }

}

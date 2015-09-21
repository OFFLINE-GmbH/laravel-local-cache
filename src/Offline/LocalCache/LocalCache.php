<?php namespace Offline\LocalCache;

use InvalidArgumentException;
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
     * RegEx to extract URLs
     *
     * @var string
     */
    protected $urlRegEx = '/https?\:\\\?\/\\\?\/[^\"\'\<]+/i';
    /**
     * Maximum file size.
     *
     * @var int
     */
    private $maxFileSize;


    /**
     * Constructor
     *
     * @param     $cachePath
     * @param     $baseUrl
     * @param Ttl $ttl
     * @param     $maxFileSize
     */
    public function __construct($cachePath, $baseUrl, Ttl $ttl, $maxFileSize)
    {
        if ( ! $this->checkCachePath($cachePath)) {
            throw new InvalidArgumentException("${cachePath} does not exist and cannot be created!");
        }

        $this->cachePath   = $cachePath;
        $this->ttl         = $ttl;
        $this->baseUrl     = $baseUrl;
        $this->maxFileSize = $maxFileSize;

        // $this->cleanUp();
    }

    /**
     * Returns the CachePath.
     *
     * @return array
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Returns the BaseUrl.
     *
     * @return array
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Adds a url to the cache.
     *
     * @param string $url
     */
    public function addUrl($url)
    {
        $this->cacheObjects[$url] = new CacheObject(new Url($url), $this->ttl, $this->cachePath, $this->maxFileSize);
    }

    /**
     * Returns a url to a cached file.
     *
     * @param string $url
     *
     * @return string
     */
    public function getUrl($url)
    {
        if ( ! array_key_exists($url, $this->cacheObjects)) {
            return $url;
        }

        $cacheObject = $this->cacheObjects[$url];

        // External URL, contains a slash
        if (strpos((string)$cacheObject, '/') !== false) {
            return $cacheObject;
        }

        $fullUrl = (string)$this->baseUrl . '/' . $cacheObject;
        if($cacheObject->getUrl()->escape === true) {
            $fullUrl = str_replace('/', '\/', $fullUrl);
        }

        return $fullUrl;
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

    /**
     * Remove the currently active CacheObjects from disk.
     */
    public function flush()
    {
        foreach ($this->cacheObjects as $object) {
            $object->remove();
        }
    }

    /**
     * Remove invalid cache objects.
     */
    private function cleanUp()
    {
        foreach (glob($this->getCachePath() . '/*') as $file) {

            if(strpos('mimeMap.json', $file) !== false) continue;

            if (time() - filemtime($file) >= $this->ttl->inSeconds()) {
                @unlink($file);
            }
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
        preg_match_all($this->urlRegEx, $html, $matches);

        foreach ($matches[0] as $url) {
            $this->addUrl($url);
        }

        return $this->cacheObjects;
    }


    /**
     * Replaces URLs in a string.
     *
     * @param $html
     *
     * @return mixed
     */
    private function replaceUrls($html)
    {
        preg_match_all($this->urlRegEx, $html, $matches);

        foreach ($matches[0] as $url) {
            $html = str_replace($url, $this->getUrl($url), $html);
        }

        return $html;
    }

    /**
     * @param $cachePath
     *
     * @return bool
     */
    private function checkCachePath($cachePath)
    {
        if (is_dir($cachePath) && is_writable($cachePath)) {
            return true;
        }

        return @mkdir($cachePath);
    }

}

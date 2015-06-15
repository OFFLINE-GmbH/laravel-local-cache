<?php

namespace Offline\LocalCache;


use Offline\LocalCache\ValueObjects\Ttl;
use Offline\LocalCache\ValueObjects\Url;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class CacheObject
{
    /**
     * Remote URL
     *
     * @var Url
     */
    protected $url;
    /**
     * Storage Path of this CacheObject
     * @var string
     */
    protected $basePath;
    /**
     * Time to live
     *
     * @var Ttl
     */
    protected $ttl;

    public function __construct(Url $url, Ttl $ttl, $basePath)
    {
        $this->url      = $url;
        $this->basePath = rtrim($basePath, '/');
        $this->ttl      = $ttl;
    }

    /**
     * Checks if the CacheObject is cached.
     *
     * @return bool
     */
    public function isCached()
    {
        return file_exists($this->getCachePath()) && $this->isValid();
    }

    /**
     * Returns the CacheObject's filesystem path.
     *
     * @return string
     */
    public function getCachePath()
    {
        return $this->basePath . '/' . $this->url->toHash();
    }

    /**
     * Checks if the CacheObject's TTL has expired.
     *
     * @return bool
     */
    private function isValid()
    {
        // TTL has expired
        if (time() - filemtime($this->getCachePath()) > $this->ttl->inSeconds()) {
            @unlink($this->getCachePath());

            return false;
        }

        return true;
    }

    /**
     * Stores a file in the filesystem.
     *
     * @return string
     */
    public function store()
    {
        file_put_contents($this->getCachePath(), $this->getRemoteContents());

        return $this->url->toHash();
    }

    /**
     * Removes a file from the filesystem.
     *
     * @return string
     */
    public function remove()
    {
        @unlink($this->getCachePath());
    }

    public function getRemoteContents()
    {
        return file_get_contents($this->url);
    }

    public function getLocalContents()
    {
        return file_get_contents($this->getCachePath());
    }

    public function __toString()
    {
        return $this->isCached() ? (string)$this->url->toHash() : (string)$this->url;
    }
}

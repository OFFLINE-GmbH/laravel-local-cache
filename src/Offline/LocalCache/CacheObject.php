<?php

namespace Offline\LocalCache;


use Offline\LocalCache\ValueObjects\Ttl;
use Offline\LocalCache\ValueObjects\Url;

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
        $this->ttl      = $ttl;
        $this->basePath = rtrim($basePath, '/');

        $this->store();
    }

    /**
     * Stores a file in the filesystem.
     *
     * @return string
     */
    public function store()
    {
        if ($this->isCached()) {
            return $this->url->toHash();
        }

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
     * Returns the cached file contents.
     *
     * @return string
     */
    public function getContents()
    {
        return file_get_contents($this->getCachePath());
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
        if (time() - filemtime($this->getCachePath()) >= $this->ttl->inSeconds()) {
            $this->remove();

            return false;
        }

        return true;
    }

    private function getRemoteContents()
    {
        return file_get_contents((string)$this->url);
    }

    public function __toString()
    {
        return $this->isCached() ? (string)$this->url->toHash() : (string)$this->url;
    }
}

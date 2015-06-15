<?php

namespace Offline\LocalCache;


use Offline\LocalCache\ValueObjects\Ttl;
use Offline\LocalCache\ValueObjects\Url;

class CacheObject
{
    protected $url;
    protected $basePath;
    protected $ttl;

    public function __construct(Url $url, Ttl $ttl, $basePath)
    {
        $this->url      = $url;
        $this->basePath = trim($basePath, '/');
        $this->ttl      = $ttl;
    }

    public function isCached()
    {
        return file_exists($this->getCachePath()) && $this->isValid();
    }

    public function getCachePath()
    {
        return $this->basePath . '/' . $this->url->toHash();
    }

    /**
     * @return bool
     */
    private function isValid()
    {
        return time() - filemtime($this->getCachePath()) < $this->ttl->inSeconds();
    }
}

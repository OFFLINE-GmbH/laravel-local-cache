<?php

namespace Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Ttl;
use Offline\LocalCache\ValueObjects\Url;

/**
 * Class CacheObject
 * @package Offline\LocalCache
 */
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
    /**
     * URL to Object
     *
     * @var Ttl
     */
    protected $objectUrl;

    /**
     * @param Url $url
     * @param Ttl $ttl
     * @param     $basePath
     */
    public function __construct(Url $url, Ttl $ttl, $basePath)
    {
        $this->url      = $url;
        $this->ttl      = $ttl;
        $this->basePath = rtrim($basePath, '/');

        $this->download();
    }

    /**
     * Stores a file in the filesystem.
     *
     * @param $data
     *
     * @return string
     */
    public function store($data)
    {
        if ($this->isValid()) {
            return;
        }

        file_put_contents($this->getCachePath(), $data);

        MimeMap::storeToMap($this->basePath . '/mimeMap.json', $this->url);

        return $this->url->toHash();
    }

    /**
     * Downloads the remote file and stores if necessary.
     *
     * @return bool
     */
    private function download()
    {
        list($data, $status) = $this->makeCurlRequest();

        $this->objectUrl = $this->url->toHash();

        if ($status === true) {
            $this->store($data);

            return true;
        }

        if ( ! $this->isCached()) {
            $this->objectUrl = (string)$this->url;
        }
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
        return file_exists($this->getCachePath());
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
    public function isValid()
    {
        if ( ! $this->isCached()) {
            return false;
        }

        // TTL has expired
        if (time() - filemtime($this->getCachePath()) >= $this->ttl->inSeconds()) {
            $this->remove();

            return false;
        }

        return true;
    }

    /**
     * Makes a curl request and downloads the contents
     * of the remote file.
     *
     * @return array
     */
    private function makeCurlRequest()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $this->url);

        $data     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 400);

        return [$data, $status];
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->objectUrl;
    }

}

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
     * Maximum file size.
     *
     * @var int
     */
    private $maxFileSize;

    /**
     * @param Url $url
     * @param Ttl $ttl
     * @param     $basePath
     * @param     $maxFileSize
     */
    public function __construct(Url $url, Ttl $ttl, $basePath, $maxFileSize)
    {
        $this->url      = $url;
        $this->ttl      = $ttl;
        $this->basePath = rtrim($basePath, '/');
        $this->maxFileSize = $maxFileSize;

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
        list($data, $status) = $this->downloadRemoteFile();

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
     * Returns the CacheObject's URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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
    private function downloadRemoteFile()
    {
        if ($this->getRemoteFileSize($this->url) > $this->maxFileSize) {
            return [null, false];
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->url);

        $data     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 400);

        return [$data, $status];
    }

    /**
     * Returns the size of a file without downloading it, or -1 if the file
     * size could not be determined.
     *
     * @param $url
     *
     * @return int
     */
    function getRemoteFileSize($url)
    {

        $result = -1;

        $curl = curl_init($url);

        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($curl);
        curl_close($curl);

        if ($data) {
            $content_length = "unknown";
            $status         = "unknown";

            if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
                $status = (int)$matches[1];
            }

            if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
                $content_length = (int)$matches[1];
            }

            if ($status == 200 || ($status > 300 && $status <= 308)) {
                $result = $content_length;
            }
        }

        return $result;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->objectUrl;
    }

}

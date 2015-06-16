<?php namespace Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Url;

/**
 * Class MimeMap
 * @package Offline\LocalCache
 */
class MimeMap
{
    /**
     * @var
     */
    protected $mapFile;

    /**
     * Constructor
     *
     * @param $mapFile
     */
    public function __construct($mapFile)
    {
        $this->mapFile = $mapFile;
        $this->map     = $this->getMap();
    }

    /**
     * Returns the stored MimeMap.
     *
     * @return array
     */
    public function getMap()
    {
        if (file_exists($this->mapFile)) {
            return json_decode(file_get_contents($this->mapFile), true);
        }

        return [];
    }

    /**
     * Read a MapFile from disk.
     *
     * @param $mapFile
     *
     * @return mixed
     */
    public static function readMapFile($mapFile)
    {
        return (new static($mapFile))->getMap();
    }

    /**
     * Add a URL to a MapFile.
     *
     * @param     $mapFile
     * @param Url $url
     *
     * @return mixed
     */
    public static function storeToMap($mapFile, Url $url)
    {
        return (new static($mapFile))->add($url);
    }

    /**
     * Adds a Url to the MimeMap.
     *
     * @param Url $url
     */
    private function add(Url $url)
    {
        $this->map[$url->toHash()] = $this->getMime($url);
        $this->perist();
    }

    /**
     * Saves to MimeMap to disk.
     */
    private function perist()
    {
        file_put_contents($this->mapFile, json_encode($this->map));
    }

    /**
     * Determines the MimeType of a remote file.
     *
     * @param Url $url
     *
     * @return string
     */
    private function getMime(Url $url)
    {
        $ch = curl_init((string)$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        return $mime !== false ? $mime : 'application/octet-stream';
    }

}

<?php

namespace Offline\LocalCache\ValueObjects;

use InvalidArgumentException;

/**
 * Class Url
 * @package Offline\LocalCache\ValueObjects
 */
class Url
{
    /**
     * URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Whether or not the URL has to be escaped.
     *
     * @var bool
     */
    public $escape;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        if(strpos($url, '\/') !== false) {
            $this->escape = true;
            $url = str_replace('\/', '/', $url);
        }

        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("${url} is not a valid url");
        }
        $this->url = $url;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->url;
    }

    /**
     * Return the hashed URL.
     *
     * @return string
     */
    public function toHash()
    {
        return md5($this->url);
    }
}

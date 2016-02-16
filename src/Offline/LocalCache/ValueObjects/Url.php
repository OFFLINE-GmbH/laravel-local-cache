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
     * Whether or not the URL has to be replaced.
     *
     * @var bool
     */
    public $replace = true;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        if (strpos($url, '\/') !== false) {
            $this->escape = true;
            $url          = str_replace('\/', '/', $url);
        }

        $checkUrl = $url;
        if (starts_with($url, '@')) {
            $checkUrl      = substr($url, 1);
            $this->replace = false;
        }

        if ( ! filter_var($checkUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("${checkUrl} is not a valid url");
        }

        $this->url = $checkUrl;
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

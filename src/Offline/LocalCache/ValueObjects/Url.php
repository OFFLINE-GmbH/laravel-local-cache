<?php

namespace Offline\LocalCache\ValueObjects;

use InvalidArgumentException;

class Url
{
    protected $url;

    public function __construct($url)
    {
        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("${url} is not a valid url");
        }
        $this->url = $url;
    }

    public function __toString()
    {
        return (string)$this->url;
    }

    public function toHash()
    {
        return md5($this->url);
    }
}

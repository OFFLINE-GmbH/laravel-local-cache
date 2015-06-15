<?php

namespace Offline\LocalCache\ValueObjects;

use InvalidArgumentException;

class Ttl
{
    protected $seconds;

    public function __construct($seconds)
    {
        if ($seconds < 0) {
            throw new InvalidArgumentException("TTL needs to be greater than or equal to 0");
        }
        $this->seconds = $seconds;
    }

    public function __toString()
    {
        return (string)$this->seconds;
    }


    public function inSeconds()
    {
        return (int)$this->seconds;
    }
}

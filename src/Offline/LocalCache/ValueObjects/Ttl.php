<?php

namespace Offline\LocalCache\ValueObjects;

use InvalidArgumentException;

/**
 * Class Ttl
 * @package Offline\LocalCache\ValueObjects
 */
class Ttl
{
    /**
     * Time to live in seconds.
     * @var int
     */
    protected $seconds;

    /**
     * @param $seconds
     */
    public function __construct($seconds)
    {
        if ($seconds < 0) {
            throw new InvalidArgumentException("TTL needs to be greater than or equal to 0");
        }
        $this->seconds = $seconds;
    }

    /**
     * @return int
     */
    public function inSeconds()
    {
        return (int)$this->seconds;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->seconds;
    }


}

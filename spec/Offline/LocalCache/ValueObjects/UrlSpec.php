<?php

namespace spec\Offline\LocalCache\ValueObjects;

use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UrlSpec extends ObjectBehavior
{
    const validURL = 'http://www.offlinegmbh.ch/file.jpg';
    const invalidURL = 'http:/invalid/url';

    function let()
    {
        $this->beConstructedWith(self::validURL);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\ValueObjects\Url');
    }

    function it_rejects_invalid_url()
    {
        $this->shouldThrow(new InvalidArgumentException(self::invalidURL . ' is not a valid url'))
             ->during('__construct', [self::invalidURL]);
    }

    function it_returns_a_hash()
    {
        $this->toHash()->shouldReturn(md5(static::validURL));
    }
}

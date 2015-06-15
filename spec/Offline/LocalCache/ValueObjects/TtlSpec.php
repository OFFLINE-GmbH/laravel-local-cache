<?php

namespace spec\Offline\LocalCache\ValueObjects;

use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TtlSpec extends ObjectBehavior
{
    const ttl = 20;

    function let()
    {
        $this->beConstructedWith(self::ttl);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\ValueObjects\Ttl');
    }

    function it_rejects_invalid_ttl()
    {
        $this->shouldThrow(new InvalidArgumentException('TTL needs to be greater than or equal to 0'))
             ->during('__construct', [-10]);
    }

    function it_returns_itself_in_seconds()
    {
        $this->inSeconds()->shouldReturn((int)self::ttl);
    }
}

<?php

namespace spec\Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Url;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MimeMapSpec extends ObjectBehavior
{
    private $mapFile;

    public function __construct()
    {
        $this->mapFile = realpath(__DIR__ . '/../../tmp') . '/mimeMap.json';
    }

    function let()
    {
        $this->beConstructedWith($this->mapFile);
    }

    function letgo()
    {
        @unlink($this->mapFile);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\MimeMap');
    }

    function it_returns_a_mime_map()
    {
        $this->getMap()->shouldReturn([]);
    }

    function it_adds_to_the_mime_map()
    {
        $this->storeToMap($this->mapFile, new Url('http://abc'));
        $this->getMap()->shouldHaveCount(1);
    }
}

<?php

namespace spec\Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Ttl;
use org\bovigo\vfs\vfsStream;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocalCacheSpec extends ObjectBehavior
{
    private $contentString = <<<STR
<html>
<p>http://www.url.com/file</p>
<a href="https://localhost/file">http://localhost/file/a/b</a>
<div>http://localhost/file/a/b</div>
</html>
STR;

    function let()
    {
        vfsStream::setup('cachePath');
        $this->beConstructedWith(vfsStream::url('chachePath'), new Ttl(20));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\LocalCache');
    }

    function it_extracts_urls()
    {
        $this->extractUrls($this->contentString)->shouldReturn([
            'http://www.url.com/file',
            'https://localhost/file',
            'http://localhost/file/a/b',
            'http://localhost/file/a/b',
        ]);
    }
}

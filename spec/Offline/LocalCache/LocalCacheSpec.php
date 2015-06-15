<?php

namespace spec\Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Ttl;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocalCacheSpec extends ObjectBehavior
{
    const baseUrl = 'http://localhost';

    private $contentString = <<<STR
<html>
<p>http://www.url.com/file</p>
<a href="https://localhost/file">http://localhost/file/a/b</a>
<div>http://localhost/file/a/b</div>
</html>
STR;

    private $localContentString = <<<STR
<html>
<p>http://localhost/index.html</p>
<div>http://localhost/</div>
</html>
STR;
    private $replacedLocalContentString = <<<STR
<html>
<p>http://url/758bcef4c2262091eca810a6022c73c1</p>
<div>http://url/c9db569cb388e160e4b86ca1ddff84d7</div>
</html>
STR;

    function let()
    {
        vfsStream::setup('cachePath');
        $this->beConstructedWith(vfsStream::url('chachePath'), self::baseUrl, new Ttl(20));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\LocalCache');
    }

    function it_extracts_urls()
    {
        $this->extractUrls($this->contentString)->shouldHaveCount(3);
    }

    function it_replaces_urls()
    {
        $this->beConstructedWith(__DIR__, 'http://url', new Ttl(20));
        $this->getCachedHtml($this->localContentString)->shouldReturn($this->replacedLocalContentString);
        $this->flush();
    }

    public function getMatchers()
    {
        return [
            'haveDeletedOldCacheObjects' => function ($actual, $file) {
                return VfsStreamWrapper::getRoot()->hasChild($file);
            },
        ];
    }
}

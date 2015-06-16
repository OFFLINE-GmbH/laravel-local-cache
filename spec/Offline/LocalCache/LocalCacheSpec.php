<?php

namespace spec\Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Ttl;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocalCacheSpec extends ObjectBehavior
{
    const baseUrl = 'http://localhost';
    private $tmp;

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

    function __construct()
    {
        if(getenv('TRAVIS') == true) {
            $this->tmp = realpath(__DIR__ . '/../../tmp');
        } else {
            $this->tmp = getenv('TRAVIS_BUILD_DIR') . '/tmp';
        }
    }

    function let()
    {
        $this->beConstructedWith($this->tmp, self::baseUrl, new Ttl(20));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\LocalCache');
    }

    function it_replaces_urls()
    {
        $this->beConstructedWith($this->tmp, 'http://url', new Ttl(20));
        $this->getCachedHtml($this->localContentString)->shouldReturn($this->replacedLocalContentString);
        $this->flush();
    }

    public function getMatchers()
    {
        return [
            'haveDeletedOldCacheObjects' => function ($actual, $file) {
                return file_exists($this->tmp . '/' . $file);
            },
        ];
    }
}

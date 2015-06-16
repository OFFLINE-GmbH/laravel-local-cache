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

    private $validContentString = <<<STR
<html>
<p>http://en.wikipedia.org/w/api.php?action=query&titles=Albert%20Einstein&prop=info&format=json&callback=foo</p>
<div>http://de.wikipedia.org/w/api.php?action=query&titles=Albert%20Einstein&prop=info&format=json&callback=foo</div>
</html>
STR;
    private $replacedValidContentString = <<<STR
<html>
<p>http://url/8bdc0b28c0e072fc090330c2e8124d9b</p>
<div>http://url/788cf50ad87e3c43a1ffe27ed4b42b06</div>
</html>
STR;

    function __construct()
    {
        if(getenv('TRAVIS') == true) {
            $this->tmp = getenv('TRAVIS_BUILD_DIR') . '/spec/tmp';
        } else {
            $this->tmp = realpath(__DIR__ . '/../../tmp');
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
        $this->getCachedHtml($this->validContentString)->shouldReturn($this->replacedValidContentString);
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

<?php

namespace spec\Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Ttl;
use Offline\LocalCache\ValueObjects\Url;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CacheObjectSpec extends ObjectBehavior
{
    private $url;
    private $ttl;
    private $tmp;

    const validUrl = 'http://en.wikipedia.org/w/api.php?action=query&titles=Albert%20Einstein&prop=info&format=json&callback=foo';
    const localhost = 'http://localhost/index.html';

    function __construct()
    {
        $this->url = new Url(self::validUrl);
        $this->ttl = new Ttl(20);
        if (getenv('TRAVIS') == true) {
            $this->tmp = getenv('TRAVIS_BUILD_DIR') . '/spec/tmp';
        } else {
            $this->tmp = realpath(__DIR__ . '/../..') . '/tmp';
        }
    }

    function let()
    {
        file_put_contents($this->tmp . '/bf58e23ba5001cfd9ae9cfd8c056526d', 'valid cache file');
        $this->beConstructedWith($this->url, $this->ttl, $this->tmp);
    }

    function letgo()
    {
        foreach (glob($this->tmp . '/*') as $file) {
            @unlink($file);
        }
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\CacheObject');
    }

    function it_invalidates_cached_file_that_does_not_exist()
    {
        $this->remove();
        $this->isCached()->shouldReturn(false);
    }

    function it_invalidates_cached_file_that_does_exist()
    {
        $this->beConstructedWith(new Url(self::validUrl . '/valid'), new Ttl(0), $this->tmp);
        $this->isValid()->shouldReturn(false);
    }

    function it_validates_cached_file()
    {
        $this->beConstructedWith(new Url(self::validUrl . '/valid'), $this->ttl, $this->tmp);
        $this->isCached()->shouldReturn(true);
    }

    function it_returns_its_cache_path()
    {
        $this->getCachePath()->shouldReturn($this->tmp . '/' . $this->url->toHash());
    }

    function it_caches_a_file()
    {
        $localUrl = new Url(self::localhost);

        $this->beConstructedWith($localUrl, $this->ttl, $this->tmp);

        $this->remove();

        $this->store('abc')->shouldReturn($localUrl->toHash());

        $this->shouldHaveCreatedTheFile($localUrl->toHash());
    }

    function it_removes_an_old_file()
    {
        $localUrl = new Url(self::localhost);

        $this->beConstructedWith($localUrl, new Ttl(0), $this->tmp);

        $this->store('abc')->shouldReturn($localUrl->toHash());

        $this->shouldHaveCreatedTheFile($localUrl->toHash());

        $this->isValid()->shouldReturn(false);

        $this->shouldHaveRemovedTheFile($localUrl->toHash());

    }

    public function getMatchers()
    {
        return [
            'haveCreatedTheFile' => function ($actual, $file) {
                return file_exists($this->tmp . '/' . $file);
            },
            'haveRemovedTheFile' => function ($actual, $file) {
                return ! file_exists($this->tmp . '/' . $file);
            },
        ];
    }
}

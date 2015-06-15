<?php

namespace spec\Offline\LocalCache;

use Offline\LocalCache\ValueObjects\Ttl;
use Offline\LocalCache\ValueObjects\Url;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CacheObjectSpec extends ObjectBehavior
{
    private $url;
    private $ttl;

    const validUrl = 'http://www.offlinegmbh.ch/valid.jpg';
    const localhost = 'http://localhost/index.html';

    function __construct()
    {
        $this->url = new Url(self::validUrl);
        $this->ttl = new Ttl(20);
    }

    function let()
    {
        vfsStream::setup('cachePath', null, [
            'fa97a404df81d98ff3b04c10f5dbecab' => 'valid cache file',
        ]);
        $this->beConstructedWith($this->url, $this->ttl, vfsStream::url('chachePath'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Offline\LocalCache\CacheObject');
    }

    function it_invalidates_cached_file_that_does_not_exist()
    {
        $this->isCached()->shouldReturn(false);
    }

    function it_invalidates_cached_file_that_does_exist()
    {
        $this->beConstructedWith(new Url(self::validUrl . '/valid'), new Ttl(0), vfsStream::url('cachePath'));
        sleep(1); // let the ttl expire
        $this->isCached()->shouldReturn(false);
    }

    function it_validates_cached_file()
    {
        $this->beConstructedWith(new Url(self::validUrl . '/valid'), $this->ttl, vfsStream::url('cachePath'));
        $this->isCached()->shouldReturn(true);
    }

    function it_returns_its_cache_path()
    {
        $this->getCachePath()->shouldReturn('vfs://chachePath/' . $this->url->toHash());
    }

    function it_caches_a_file()
    {
        $localUrl = new Url(self::localhost);

        $this->beConstructedWith($localUrl, $this->ttl, vfsStream::url('cachePath'));

        $this->store()->shouldReturn($localUrl->toHash());

        $this->shouldHaveCreatedTheFile($localUrl->toHash());
    }

    function it_removes_an_old_file()
    {
        $localUrl = new Url(self::localhost);

        $this->beConstructedWith($localUrl, new Ttl(0), vfsStream::url('cachePath'));

        $this->store()->shouldReturn($localUrl->toHash());

        $this->shouldHaveCreatedTheFile($localUrl->toHash());

        sleep(1);

        $this->isCached()->shouldReturn(false);

        $this->shouldHaveRemovedTheFile($localUrl->toHash());

    }

    public function getMatchers()
    {
        return [
            'haveCreatedTheFile' => function ($actual, $file) {
                return VfsStreamWrapper::getRoot()->hasChild($file);
            },
            'haveRemovedTheFile' => function ($actual, $file) {
                return ! VfsStreamWrapper::getRoot()->hasChild($file);
            },
        ];
    }
}

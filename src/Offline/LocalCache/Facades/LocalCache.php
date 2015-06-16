<?php namespace Offline\LocalCache\Facades;

use Illuminate\Support\Facades\Facade;

class LocalCache extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'LocalCache';
    }

}

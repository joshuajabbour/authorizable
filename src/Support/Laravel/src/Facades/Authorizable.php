<?php

namespace JoshuaJabbour\Authorizable\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Authorizable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'authorizable';
    }
}

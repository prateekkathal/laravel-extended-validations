<?php

namespace PrateekKathal\Validation\Facades;

use Illuminate\Support\Facades\Facade;

class Validator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'extended-validator';
    }
}

<?php

namespace Just\Warehouse\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Just\Warehouse\Stock
 */
class Stock extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stock';
    }
}

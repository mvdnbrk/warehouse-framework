<?php

namespace Just\Warehouse\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Just\Warehouse\Stock
 */
class Stock extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'stock';
    }
}

<?php

namespace Just\Warehouse;

use Illuminate\Support\Facades\Facade;

class WarehouseFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'warehouse';
    }
}

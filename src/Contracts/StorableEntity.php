<?php

namespace Just\Warehouse\Contracts;

use Just\Warehouse\Models\Location;

interface StorableEntity
{
    /**
     * Move the inventory model to another location.
     *
     * @param  \Just\Warehouse\Models\Location  $location
     * @return bool
     */
    public function move(Location $location);
}

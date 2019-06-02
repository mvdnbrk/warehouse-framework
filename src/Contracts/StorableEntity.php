<?php

namespace Just\Warehouse\Contracts;

use Just\Warehouse\Models\Location;

interface StorableEntity
{
    /**
     * Move the inventory model to another location.
     *
     * @param  Location  $location
     * @return bool
     */
    public function moveTo(Location $location);
}

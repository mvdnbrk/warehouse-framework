<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Models\Location;
use LogicException;

class LocationObserver
{
    /**
     * @throws \LogicException
     */
    public function deleting(Location $location): void
    {
        if ($location->inventory()->withTrashed()->count()) {
            throw new LogicException('Location can not be deleted because it has inventory.');
        }
    }
}

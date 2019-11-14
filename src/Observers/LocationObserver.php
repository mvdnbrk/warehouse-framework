<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Models\Location;
use LogicException;

class LocationObserver
{
    /**
     * Handle the Location "deleting" event.
     *
     * @param  \Just\Warehouse\Models\Location  $location
     * @return void
     *
     * @throws \LogicException
     */
    public function deleting(Location $location)
    {
        if ($location->inventory()->withTrashed()->count()) {
            throw new LogicException('Location can not be deleted because it has inventory.');
        }
    }
}

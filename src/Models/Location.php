<?php

namespace Just\Warehouse\Models;

use LogicException;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Location extends AbstractModel
{
    /**
     * The inventory associated with this location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Add inventory to this location with a GTIN.
     *
     * @param  string  $value
     * @return \Just\Warehouse\Models\Inventory
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function addInventory($value)
    {
        return $this->inventory()->create([
            'gtin' => $value,
        ]);
    }

    /**
     * Move inventory to another location with a GTIN.
     *
     * @param  string  $value
     * @param  \Just\Warehouse\Models\Location  $location
     * @return \Just\Warehouse\Models\Inventory  $inventory
     * @throws \LogicException
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function move($value, self $location)
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException;
        }

        if (! $location->exists) {
            throw new LogicException('Location does not exist.');
        }

        return tap($this->inventory()->whereGtin($value)->first(), function ($model) use ($value, $location) {
            if ($model === null) {
                throw (new ModelNotFoundException)->setModel(Inventory::class, [$value]);
            }

            $model->update([
                'location_id' => $location->id,
            ]);
        });
    }

    /**
     * Remove inventory from this location with a GTIN.
     *
     * @param  string  $value
     * @return bool
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function removeInventory($value)
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException;
        }

        if (! $model = $this->inventory()->whereGtin($value)->oldest()->first()) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException($value))->setModel($this);
        }

        return $model->delete();
    }

    /**
     * Remove all inventory from this location.
     *
     * @return int
     */
    public function removeAllInventory()
    {
        return $this->inventory()->delete();
    }
}

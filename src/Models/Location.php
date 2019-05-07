<?php

namespace Just\Warehouse\Models;

use Just\Warehouse\Exceptions\InvalidGtinException;

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
     * Add inventory to this location.
     *
     * @param  string  $value
     * @return \Just\Warehouse\Models\Inventory
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function addInventory($value)
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException($value);
        }

        return $this->inventory()->create([
            'gtin' => $value,
        ]);
    }

    /**
     * Remove inventory from this location.
     *
     * @param  string  $value
     * @return bool
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function removeInventory($value)
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException($value);
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

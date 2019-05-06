<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Just\Warehouse\Exceptions\InvalidGtinException;

class Location extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('warehouse.database_connection');
    }

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
     * Delete inventory from this location.
     *
     * @param  string  $gtin
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
}

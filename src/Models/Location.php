<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Just\Warehouse\Exceptions\InvalidGtinException;
use LogicException;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Location extends AbstractModel
{
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Add inventory to this location with a GTIN.
     *
     * @param  string  $value
     * @param  int  $amount
     * @return \Just\Warehouse\Models\Inventory|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function addInventory($value, $amount = 1)
    {
        if ($amount < 1) {
            return $this->newCollection();
        }

        $instances = $this->newCollection(array_map(function () use ($value) {
            return $this->inventory()->create([
                'gtin' => $value,
            ]);
        }, range(1, $amount)));

        return $amount === 1 ? $instances->first() : $instances;
    }

    /**
     * Move inventory to another location with a GTIN.
     *
     * @param  string  $value
     * @param  \Just\Warehouse\Models\Location  $location
     * @return \Just\Warehouse\Models\Inventory  $inventory
     *
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

        if ($location->is($this)) {
            throw new LogicException("Inventory can not be be moved to it's own location.");
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
     * Move multiple inventory models to another location.
     *
     * @param  array  $values
     * @param  \Just\Warehouse\Models\Location  $location
     * @return array
     */
    public function moveMany(array $values, self $location): array
    {
        $models = collect();

        DB::transaction(function () use ($values, $location, $models) {
            collect($values)->each(function ($value) use ($location, $models) {
                $models->push($this->move($value, $location));
            });
        });

        return $models->all();
    }

    /**
     * Remove inventory from this location with a GTIN.
     *
     * @param  string  $value
     * @return bool
     *
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function removeInventory($value)
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException;
        }

        if (! $model = $this->inventory()->whereGtin($value)->oldest()->first()) {
            throw (new ModelNotFoundException)->setModel(Inventory::class, [$value]);
        }

        return $model->delete();
    }

    public function removeAllInventory(): int
    {
        return $this->inventory()->delete();
    }
}

<?php

namespace Just\Warehouse\Models;

use Just\Warehouse\Exceptions\InvalidGtinException;

class Order extends AbstractModel
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * The order lines associated with this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    /**
     * Add an order line.
     *
     * @param  string  $value
     * @return \Just\Warehouse\Models\OrderLine
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function addLine($value)
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException;
        }

        return $this->lines()->create([
            'gtin' => $value,
        ]);
    }
}

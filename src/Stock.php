<?php

namespace Just\Warehouse;

use Illuminate\Database\Eloquent\Builder;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Reservation;

class Stock
{
    /**
     * @var string
     */
    protected $gtin;

    public function available(): int
    {
        return Inventory::join('reservation', 'inventories.id', '=', 'reservation.inventory_id', 'left')
            ->whereNull('reservation.inventory_id')
            ->when($this->gtin, function (Builder $query) {
                return $query->whereGtin($this->gtin);
            })
            ->count();
    }

    public function backorder(): int
    {
        return Reservation::whereNull('inventory_id')
            ->when($this->gtin, function (Builder $query) {
                return $query
                    ->join('order_lines', 'order_lines.id', '=', 'reservation.order_line_id')
                    ->where('order_lines.gtin', '=', $this->gtin);
            })
            ->count();
    }

    public function reserved(): int
    {
        return Inventory::join('reservation', 'inventories.id', '=', 'reservation.inventory_id', 'left')
            ->whereNotNull('reservation.inventory_id')
            ->when($this->gtin, function (Builder $query) {
                return $query->whereGtin($this->gtin);
            })
            ->count();
    }

    /**
     * Set a GTIN.
     *
     * @param  string  $value
     * @return $this
     *
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function gtin($value)
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException;
        }

        $this->gtin = $value;

        return $this;
    }
}

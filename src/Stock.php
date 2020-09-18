<?php

namespace Just\Warehouse;

use Illuminate\Database\Eloquent\Builder;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Reservation;

class Stock
{
    protected ?string $gtin = null;

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
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function gtin(string $value): self
    {
        if (! is_gtin($value)) {
            throw new InvalidGtinException;
        }

        $this->gtin = $value;

        return $this;
    }
}

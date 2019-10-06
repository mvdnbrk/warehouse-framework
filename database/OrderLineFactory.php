<?php

use Just\Warehouse\Models\OrderLine;

class OrderLineFactory
{
    public function create(array $overrides = [])
    {
        return factory(OrderLine::class)->create($overrides);
    }

    public function make(array $overrides = [])
    {
        return factory(OrderLine::class)->make(
            array_merge($overrides, ['order_id' => null])
        );
    }
}

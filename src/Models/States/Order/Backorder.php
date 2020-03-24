<?php

namespace Just\Warehouse\Models\States\Order;

class Backorder extends OrderState
{
    public static string $name = 'backorder';

    public function label(): string
    {
        return 'in backorder';
    }
}

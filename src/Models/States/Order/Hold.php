<?php

namespace Just\Warehouse\Models\States\Order;

class Hold extends OrderState
{
    public static string $name = 'hold';

    public function label(): string
    {
        return 'on hold';
    }
}

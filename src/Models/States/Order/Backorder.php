<?php

namespace Just\Warehouse\Models\States\Order;

class Backorder extends OrderState
{
    /**
     * The name of this state.
     *
     * @var string
     */
    public static $name = 'backorder';

    /**
     * Get the displayable label of the state.
     *
     * @return string
     */
    public function label()
    {
        return 'in backorder';
    }
}

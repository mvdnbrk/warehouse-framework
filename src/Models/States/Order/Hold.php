<?php

namespace Just\Warehouse\Models\States\Order;

class Hold extends OrderState
{
    /**
     * The name of this state.
     *
     * @var string
     */
    public static $name = 'hold';

    /**
     * Get the displayable label of the state.
     *
     * @return string
     */
    public function label()
    {
        return 'on hold';
    }
}

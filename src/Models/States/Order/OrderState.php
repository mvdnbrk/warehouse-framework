<?php

namespace Just\Warehouse\Models\States\Order;

use Spatie\ModelStates\State;

abstract class OrderState extends State
{
    /**
     * Get the displayable label of the state.
     *
     * @return string
     */
    public function label()
    {
        $value = $this::$name ?: class_basename(get_called_class());

        return $value;
    }
}

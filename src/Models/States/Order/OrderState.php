<?php

namespace Just\Warehouse\Models\States\Order;

use Illuminate\Support\Str;
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
        $value = $this::$name ?? Str::snake(class_basename($this), ' ');

        return $value;
    }

    /**
     * Determine if the order state is "created".
     *
     * @return bool
     */
    public function isCreated()
    {
        return $this->is(Created::class);
    }

    /**
     * Determine if the order state is "backorder".
     *
     * @return bool
     */
    public function isBackorder()
    {
        return $this->is(Backorder::class);
    }

    /**
     * Determine if the order state is "open".
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->is(Open::class);
    }

    /**
     * Determine if the order state is "hold".
     *
     * @return bool
     */
    public function isHold()
    {
        return $this->is(Hold::class);
    }

    /**
     * Determine if the order state is "fulfilled".
     *
     * @return bool
     */
    public function isFulfilled()
    {
        return $this->is(Fulfilled::class);
    }

    /**
     * Determine if the order state is "deleted".
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->is(Deleted::class);
    }
}

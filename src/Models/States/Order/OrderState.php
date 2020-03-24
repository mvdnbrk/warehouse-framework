<?php

namespace Just\Warehouse\Models\States\Order;

use Illuminate\Support\Str;
use Spatie\ModelStates\State;

abstract class OrderState extends State
{
    public function label(): string
    {
        return $this::$name ?? Str::snake(class_basename($this), ' ');
    }

    public function isCreated(): bool
    {
        return $this->is(Created::class);
    }

    public function isBackorder(): bool
    {
        return $this->is(Backorder::class);
    }

    public function isOpen(): bool
    {
        return $this->is(Open::class);
    }

    public function isHold(): bool
    {
        return $this->is(Hold::class);
    }

    public function isFulfilled(): bool
    {
        return $this->is(Fulfilled::class);
    }

    public function isDeleted(): bool
    {
        return $this->is(Deleted::class);
    }
}

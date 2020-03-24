<?php

namespace Just\Warehouse\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\OrderLine;

class OrderLineCreated
{
    use Dispatchable, SerializesModels;

    public OrderLine $line;

    public function __construct(OrderLine $line)
    {
        $this->line = $line;
    }
}

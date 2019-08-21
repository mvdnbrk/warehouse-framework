<?php

namespace Just\Warehouse\Events;

use Just\Warehouse\Models\OrderLine;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class OrderLineCreated
{
    use Dispatchable,
        SerializesModels;

    /**
     * The order line model that was created.
     *
     * @var \Just\Warehouse\Models\OrderLine
     */
    public $line;

    /**
     * Create a new event instance.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function __construct(OrderLine $line)
    {
        $this->line = $line;
    }
}

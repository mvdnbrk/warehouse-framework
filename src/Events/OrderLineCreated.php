<?php

namespace Just\Warehouse\Events;

use Just\Warehouse\Models\OrderLine;
use Illuminate\Queue\SerializesModels;

class OrderLineCreated
{
    use SerializesModels;

    /**
     * The order line model that was created.
     *
     * @var \Just\Warehouse\Models\OrderLine
     */
    public $line;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderLine $line)
    {
        $this->line = $line;
    }
}

<?php

namespace Just\Warehouse\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Just\Warehouse\Events\OrderFulfilled;
use Just\Warehouse\Jobs\TransitionOrderStatus;
use Just\Warehouse\Models\States\Order\Fulfilled;
use LogicException;

trait ManagesOrderStatus
{
    /**
     * Mark the order as fulfilled.
     *
     * @return void
     *
     * @throws \Spatie\ModelStates\Exceptions\TransitionNotFound
     */
    public function markAsFulfilled()
    {
        $this->transitionTo(Fulfilled::class);
    }

    /**
     * Process the order to be fulfilled.
     *
     * @return void
     */
    public function process()
    {
        TransitionOrderStatus::dispatch($this);
    }
}

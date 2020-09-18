<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Hold;

class OrdersUnholdCommand extends Command
{
    /**  @var string */
    protected $signature = 'warehouse:orders:unhold';

    /**  @var string */
    protected $description = 'Un-hold orders which are expired.';

    public function handle(): int
    {
        $orders = Order::whereState('status', Hold::class)->onlyExpired()->get();

        if ($orders->isEmpty()) {
            $this->info('Nothing to un-hold.');

            return 0;
        }

        $this->info('Number of orders that will be placed back in process: '.$orders->count());

        $orders->each->unhold();

        return 0;
    }
}

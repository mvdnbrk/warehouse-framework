<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Hold;

class OrdersUnholdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:orders:unhold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Un-hold orders which are expired.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = Order::whereState('status', Hold::class)->onlyExpired()->get();

        if ($orders->isEmpty()) {
            $this->info('Nothing to un-hold.');

            return;
        }

        $this->info('Number of orders that will be placed back in process: '.$orders->count());

        $orders->each->unhold();
    }
}

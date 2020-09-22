<?php

namespace Just\Warehouse\Tests\Console;

use Facades\OrderFactory;
use Illuminate\Support\Carbon;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;

class OrdersUnholdCommandTest extends TestCase
{
    /** @test */
    public function it_can_unhold_orders_which_are_epired()
    {
        $order = OrderFactory::state('hold')->withExpiration(10)->create();

        Carbon::setTestNow(now()->addMinutes(20));

        $this->artisan('warehouse:orders:unhold')
            ->expectsOutput('Number of orders that will be placed back in process: 1')
            ->assertExitCode(0);

        tap($order->fresh(), function (Order $order) {
            $this->assertFalse($order->status->isHold());
            $this->assertFalse($order->willExpire());
        });
    }

    /** @test */
    public function it_does_not_unhold_orders_which_will_expire_in_the_future()
    {
        $order = OrderFactory::state('hold')->withExpiration()->create();

        $this->artisan('warehouse:orders:unhold')
            ->expectsOutput('Nothing to un-hold.')
            ->assertExitCode(0);

        tap($order->fresh(), function (Order $order) {
            $this->assertTrue($order->status->isHold());
            $this->assertTrue($order->willExpire());
        });
    }

    /** @test */
    public function it_does_not_affect_created_orders()
    {
        $order = OrderFactory::withExpiration(10)->create();

        Carbon::setTestNow(now()->addMinutes(20));

        $this->artisan('warehouse:orders:unhold')
            ->expectsOutput('Nothing to un-hold.')
            ->assertExitCode(0);

        Carbon::setTestNow();

        tap($order->fresh(), function (Order $order) {
            $this->assertTrue($order->status->isCreated());
            $this->assertTrue($order->willExpire());
        });
    }
}

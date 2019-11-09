<?php

use Facades\LocationFactory;
use Faker\Factory as Faker;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;

class OrderFactory
{
    public $gtins = [];
    protected $states = [];
    protected $expiration = 0;

    public function make(array $overrides = [])
    {
        return factory(Order::class)->make($overrides);
    }

    public function state($value)
    {
        if (empty($this->gtins)) {
            $this->withLines(1);
        }

        $this->states([$value]);

        return $this;
    }

    public function withExpiration($ttl = 600)
    {
        $this->expiration = $ttl;

        return $this;
    }

    public function withLines($value)
    {
        if (is_int($value)) {
            $this->gtins = array_map(function () {
                return Faker::create()->ean13;
            }, range(1, $value));

            return $this;
        }

        $this->gtins = is_array($value) ? $value : func_get_args();

        return $this;
    }

    public function create(array $overrides = [])
    {
        $order = factory(Order::class)->states($this->states)->create(
            array_merge([
                'expires_at' => $this->expiration,
            ], $overrides)
        );

        foreach ($this->gtins as $gtin) {
            factory(OrderLine::class)->create([
                'gtin' => $gtin,
                'order_id' => $order->id,
            ]);
        }

        if (empty($this->states)) {
            return $order;
        }

        if ($this->shouldCreateInventory()) {
            LocationFactory::withInventory($this->gtins)->create();
        }

        $order->process();

        if (in_array('hold', $this->states)) {
            $order->fresh()->hold($this->expiration);
        }

        if (in_array('fulfilled', $this->states)) {
            $order->fresh()->markAsFulfilled();
        }

        if (in_array('deleted', $this->states)) {
            $order->delete();
        }

        return $order->fresh();
    }

    protected function states($value)
    {
        $this->states = is_array($value) ? $value : func_get_args();

        return $this;
    }

    protected function shouldCreateInventory()
    {
        if (in_array('fulfilled', $this->states) || in_array('open', $this->states)) {
            return true;
        }

        return false;
    }
}

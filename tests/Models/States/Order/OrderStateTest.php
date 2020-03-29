<?php

namespace Just\Warehouse\Tests\Model\States\Order;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Backorder;
use Just\Warehouse\Models\States\Order\Created;
use Just\Warehouse\Models\States\Order\Deleted;
use Just\Warehouse\Models\States\Order\Fulfilled;
use Just\Warehouse\Models\States\Order\Hold;
use Just\Warehouse\Models\States\Order\Open;
use Just\Warehouse\Models\States\Order\OrderState;
use PHPUnit\Framework\TestCase;

class OrderStateTest extends TestCase
{
    protected Order $model;

    protected function setUp(): void
    {
        $this->model = new Order;
    }

    /** @test */
    public function it_registers_states()
    {
        $this->assertCount(1, $this->model->getStates());
        $this->assertTrue($this->model->getStates()->has('status'));
        $this->assertEquals([
            Backorder::class,
            Created::class,
            Deleted::class,
            Fulfilled::class,
            Hold::class,
            Open::class,
        ], $this->model->getStatesFor('status')->all());
    }

    /** @test */
    public function it_has_a_default_state_of_created()
    {
        $this->assertCount(1, $this->model->getDefaultStates());
        $this->assertTrue($this->model->getDefaultStates()->has('status'));
        $this->assertEquals(Created::class, $this->model->getDefaultStateFor('status'));
    }

    /** @test */
    public function state_backorder_has_correct_name_and_label()
    {
        $state = new Backorder($this->model);

        $this->assertEquals('backorder', $state::$name);
        $this->assertEquals('in backorder', $state->label());
    }

    /** @test */
    public function state_created_has_correct_name_and_label()
    {
        $state = new Created($this->model);

        $this->assertEquals('created', $state::$name);
        $this->assertEquals('created', $state->label());
    }

    /** @test */
    public function state_deleted_has_correct_name_and_label()
    {
        $state = new Deleted($this->model);

        $this->assertEquals('deleted', $state::$name);
        $this->assertEquals('deleted', $state->label());
    }

    /** @test */
    public function state_fulfilled_has_correct_name_and_label()
    {
        $state = new Fulfilled($this->model);

        $this->assertEquals('fulfilled', $state::$name);
        $this->assertEquals('fulfilled', $state->label());
    }

    /** @test */
    public function state_hold_has_correct_name_and_label()
    {
        $state = new Hold($this->model);

        $this->assertEquals('hold', $state::$name);
        $this->assertEquals('on hold', $state->label());
    }

    /** @test */
    public function state_open_has_correct_name_and_label()
    {
        $state = new Open($this->model);

        $this->assertEquals('open', $state::$name);
        $this->assertEquals('open', $state->label());
    }

    /** @test */
    public function it_can_get_the_label_based_on_the_class_name()
    {
        $state = new FooBar($this->model);

        $this->assertEquals('foo bar', $state->label());
    }
}

class FooBar extends OrderState
{
    //
}

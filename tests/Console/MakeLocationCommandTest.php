<?php

namespace Just\Warehouse\Tests\Console;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;

class MakeLocationCommandTest extends TestCase
{
    /** @test */
    public function it_makes_a_new_location()
    {
        $this->artisan('warehouse:make:location')
            ->expectsQuestion('What is the name of the location?', '1')
            ->assertExitCode(0);

        $this->assertCount(1, Location::all());
        tap(Location::first(), function ($location) {
            $this->assertEquals('1', $location->name);
        });
    }
}

<?php

namespace Just\Warehouse\Tests\Console;

use Facades\LocationFactory;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Tests\TestCase;

class MakeLocationCommandTest extends TestCase
{
    /** @test */
    public function it_makes_a_new_location()
    {
        $this->artisan('warehouse:make:location')
            ->expectsQuestion('What is the name of the location?', 'test-name')
            ->expectsOutput('Location test-name created successfully.')
            ->assertExitCode(0);

        $this->assertCount(1, Location::all());
        tap(Location::first(), function ($location) {
            $this->assertEquals('test-name', $location->name);
        });
    }

    /** @test */
    public function a_location_name_is_rquired()
    {
        $this->artisan('warehouse:make:location')
            ->expectsQuestion('What is the name of the location?', '')
            ->expectsOutput('A location name is required!')
            ->assertExitCode(1);

        $this->assertCount(0, Location::all());
    }

    /** @test */
    public function a_location_name_may_not_exceed_24_charachters()
    {
        $this->artisan('warehouse:make:location')
            ->expectsQuestion('What is the name of the location?', 'a-name-that-is-longer-than-24-chars')
            ->expectsOutput('The location name is too long (maximum is 24 characters).')
            ->assertExitCode(1);

        $this->assertCount(0, Location::all());
    }

    /** @test */
    public function a_location_name_must_be_unique()
    {
        LocationFactory::create([
            'name' => 'test-name',
        ]);

        $this->artisan('warehouse:make:location')
            ->expectsQuestion('What is the name of the location?', 'test-name')
            ->expectsOutput('A location with that name already exists!')
            ->assertExitCode(1);

        $this->assertCount(1, Location::all());
    }
}

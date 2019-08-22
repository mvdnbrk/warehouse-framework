<?php

namespace Just\Warehouse\Tests\Console;

use Just\Warehouse\Tests\TestCase;

class FreshCommandTest extends TestCase
{
    /** @test */
    public function it_can_rerun_the_database_migrations()
    {
        $this->artisan('warehouse:migrate:fresh')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_rerun_the_database_migrations_with_force_option()
    {
        $this->artisan('warehouse:migrate:fresh --force')
            ->assertExitCode(0);
    }
}

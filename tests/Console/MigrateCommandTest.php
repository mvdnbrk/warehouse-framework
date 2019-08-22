<?php

namespace Just\Warehouse\Tests\Console;

use Just\Warehouse\Tests\TestCase;

class MigrateCommandTest extends TestCase
{
    /** @test */
    public function it_can_run_the_database_migrations()
    {
        $this->artisan('warehouse:migrate')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_run_the_database_migrations_with_force_option()
    {
        $this->artisan('warehouse:migrate --force')
            ->assertExitCode(0);
    }
}

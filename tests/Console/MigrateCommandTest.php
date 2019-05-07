<?php

namespace Just\Warehouse\Tests\Console;

use Just\Warehouse\Tests\TestCase;

class MigrateCommandTest extends TestCase
{
    /** @test */
    public function it_can_run_the_migration()
    {
        $this->artisan('warehouse:migrate')
            ->expectsOutput('Your warehouse is ready for use!')
            ->assertExitCode(0);
    }
}

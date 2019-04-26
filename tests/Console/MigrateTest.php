<?php

namespace Just\Warehouse\Tests\Console;

use Just\Warehouse\Tests\TestCase;

class MigrateTest extends TestCase
{
    /** @test */
    public function it_can_run_the_migration()
    {
        $this->artisan('warehouse:migrate')
            ->assertExitCode(0);
    }
}

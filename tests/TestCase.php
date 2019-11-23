<?php

namespace Just\Warehouse\Tests;

use Illuminate\Support\Facades\DB;
use Just\Warehouse\WarehouseServiceProvider;
use Mvdnbrk\EloquentExpirable\ExpirableServiceProvider;
use Mvdnbrk\Gtin\GtinServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../database/factories');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if (config('database.connections.warehouse.driver') == 'sqlite') {
            DB::connection('warehouse')->getSchemaBuilder()->enableForeignKeyConstraints();
        }
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $config = $app->get('config');

        $config->set('database.default', 'warehouse');

        $config->set('database.connections.warehouse', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            GtinServiceProvider::class,
            ExpirableServiceProvider::class,
            WarehouseServiceProvider::class,
        ];
    }
}

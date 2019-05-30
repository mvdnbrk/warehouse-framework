<?php

namespace Just\Warehouse;

use Illuminate\Support\ServiceProvider;

class WarehouseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/warehouse.php' => config_path('warehouse.php'),
            ], 'warehouse-config');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->registerCommands();
    }

    /**
     * Setup the configuration for Warehouse.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/warehouse.php', 'warehouse'
        );
    }

    /**
     * Register the Warehouse Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
                Console\Commands\MigrateCommand::class,
                Console\Commands\MakeLocationCommand::class,
            ]);
        }
    }
}

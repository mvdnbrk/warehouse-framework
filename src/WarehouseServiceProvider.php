<?php

namespace Just\Warehouse;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class WarehouseServiceProvider extends ServiceProvider
{
    use EventMap, ObserverMap;

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerEvents();
        $this->registerObservers();
    }

    public function register(): void
    {
        $this->configure();
        $this->offerPublishing();

        $this->app->bind('stock', function () {
            return new Stock;
        });
    }

    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/warehouse.php', 'warehouse'
        );
    }

    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/warehouse.php' => config_path('warehouse.php'),
            ], 'warehouse-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'warehouse-migrations');
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\FreshCommand::class,
                Console\Commands\InstallCommand::class,
                Console\Commands\MigrateCommand::class,
                Console\Commands\MakeLocationCommand::class,
                Console\Commands\OrdersUnholdCommand::class,
            ]);
        }
    }

    protected function registerEvents(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);

        foreach ($this->events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }

    protected function registerObservers(): void
    {
        foreach ($this->observers as $model => $observer) {
            $model::observe($observer);
        }
    }
}

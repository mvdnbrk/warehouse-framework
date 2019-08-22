<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:migrate {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('migrate', [
            '--database' => config('warehouse.database_connection'),
            '--path' => 'vendor/mvdnbrk/warehouse-framework/database/migrations',
            '--force' => $this->option('force'),
        ]);
    }
}

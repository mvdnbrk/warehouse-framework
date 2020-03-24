<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;

class FreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:migrate:fresh {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables and re-run all migrations';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('migrate:fresh', [
            '--path' => 'vendor/mvdnbrk/warehouse-framework/database/migrations',
            '--force' => $this->option('force'),
        ]);
    }
}

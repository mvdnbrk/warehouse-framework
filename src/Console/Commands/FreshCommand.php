<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;

class FreshCommand extends Command
{
    /**  @var string */
    protected $signature = 'warehouse:migrate:fresh {--force : Force the operation to run when in production}';

    /**  @var string */
    protected $description = 'Drop all tables and re-run all migrations';

    public function handle(): int
    {
        $this->call('migrate:fresh', [
            '--path' => 'vendor/mvdnbrk/warehouse-framework/database/migrations',
            '--force' => $this->option('force'),
        ]);

        return 0;
    }
}

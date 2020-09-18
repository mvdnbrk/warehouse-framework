<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    /** @var string */
    protected $signature = 'warehouse:migrate {--force : Force the operation to run when in production}';

    /** @var string */
    protected $description = 'Run the database migrations';

    public function handle(): int
    {
        $this->call('migrate', [
            '--path' => 'vendor/mvdnbrk/warehouse-framework/database/migrations',
            '--force' => $this->option('force'),
        ]);

        return 0;
    }
}

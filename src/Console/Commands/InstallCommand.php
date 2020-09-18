<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**  @var string */
    protected $signature = 'warehouse:install';

    /**  @var string */
    protected $description = 'Install the warehouse application';

    public function handle(): int
    {
        $this->comment('Publishing configuration...');

        $this->call('vendor:publish', ['--tag' => 'warehouse-config']);

        $this->line('');
        $this->info('Warehouse was installed successfully.');

        return 0;
    }
}

<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehosue:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the warehouse application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'warehouse-config']);

        $this->info('Warehouse was installed successfully.');
    }
}

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
    protected $signature = 'warehouse:install';

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

        $this->call('vendor:publish', ['--tag' => 'warehouse-config']);

        $this->line('');
        $this->info('Warehouse was installed successfully.');
    }
}

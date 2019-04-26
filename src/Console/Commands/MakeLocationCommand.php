<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;
use Just\Warehouse\Models\Location;

class MakeLocationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:make:location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new location';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $location = Location::create([
            'name' => $this->ask('What is the name of the location?'),
        ]);

        $this->info("Location <comment>{$location->name}</comment> created successfully.");
    }
}

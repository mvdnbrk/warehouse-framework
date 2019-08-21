<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;
use Just\Warehouse\Models\Location;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make(
            ['name' => $this->ask('What is the name of the location?')],
            $this->validationRules(),
            $this->errorMessages()
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return 1;
        }

        tap(Location::create($validator->valid()), function ($location) {
            $this->info("Location <comment>{$location->name}</comment> created successfully.");
        });
    }

    /**
     * Custom error messages for location validation.
     *
     * @return array
     */
    protected function errorMessages()
    {
        return [
            'name.required' => 'A location name is required!',
            'name.max' => 'The location name is too long (maximum is 24 characters).',
            'name.unique' => 'A location with that name already exists!',
        ];
    }

    /**
     * Validation rules for a location.
     *
     * @return array
     */
    protected function validationRules()
    {
        return [
            'name' => [
                'required',
                'max:24',
                'unique:'.config('warehouse.database_connection').'.locations',
            ],
        ];
    }
}

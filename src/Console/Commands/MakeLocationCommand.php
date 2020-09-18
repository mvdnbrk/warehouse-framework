<?php

namespace Just\Warehouse\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Just\Warehouse\Models\Location;

class MakeLocationCommand extends Command
{
    /**  @var string */
    protected $signature = 'warehouse:make:location';

    /**  @var string */
    protected $description = 'Create a new location';

    public function handle(): int
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

        return 0;
    }

    protected function errorMessages(): array
    {
        return [
            'name.required' => 'A location name is required!',
            'name.max' => 'The location name is too long (maximum is 24 characters).',
            'name.unique' => 'A location with that name already exists!',
        ];
    }

    protected function validationRules(): array
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

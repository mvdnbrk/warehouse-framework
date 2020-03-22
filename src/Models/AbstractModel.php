<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::retrieved(function (Model $model) {
            $model->makeHidden('laravel_through_key');
        });
    }

    public function getConnectionName(): string
    {
        return config('warehouse.database_connection');
    }
}

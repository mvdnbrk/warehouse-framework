<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function (Model $model) {
            $model->makeHidden('laravel_through_key');
        });
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('warehouse.database_connection');
    }
}

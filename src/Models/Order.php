<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Just\Warehouse\Models\States\Order\Backorder;
use Just\Warehouse\Models\States\Order\Created;
use Just\Warehouse\Models\States\Order\Fulfilled;
use Just\Warehouse\Models\States\Order\Open;
use Just\Warehouse\Models\States\Order\OrderState;
use Just\Warehouse\Models\Transitions\Order\OpenToFulfilled;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property array $meta
 * @property string $status
 * @property string $order_number
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection $lines
 */
class Order extends AbstractModel
{
    use HasStates,
        SoftDeletes,
        Concerns\ManagesPickList,
        Concerns\ManagesOrderStatus;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'fulfilled_at',
    ];

    /**
     * Register the states for this model.
     *
     * @return void
     */
    protected function registerStates()
    {
        $this->addState('status', OrderState::class)
            ->default(Created::class)
            ->allowTransition(Created::class, Open::class)
            ->allowTransition(Created::class, Backorder::class)
            ->allowTransition(Backorder::class, Open::class)
            ->allowTransition(Open::class, Backorder::class)
            ->allowTransition(Open::class, Fulfilled::class, OpenToFulfilled::class);
    }

    /**
     * Set the status attribute.
     *
     * @param  string  $value
     * @return void
     *
     * @throws \Just\Warehouse\Exceptions\InvalidStatusException
     */
    public function setStatusAttribute($value)
    {
        if (! $this->exists) {
            $value = new Created($this);
        }

        $this->attributes['status'] = $value;
    }

    /**
     * The order lines associated with this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    /**
     * Add an order line.
     *
     * @param  string  $value
     * @param  int  $amount
     * @return \Just\Warehouse\Models\OrderLine|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function addLine($value, $amount = 1)
    {
        if ($amount < 1) {
            return $this->newCollection();
        }

        $instances = $this->newCollection(array_map(function () use ($value) {
            return $this->lines()->create([
                'gtin' => $value,
            ]);
        }, range(1, $amount)));

        return $amount === 1 ? $instances->first() : $instances;
    }
}

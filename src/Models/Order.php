<?php

namespace Just\Warehouse\Models;

use LogicException;
use Just\Warehouse\Events\OrderFulfilled;
use Illuminate\Database\Eloquent\SoftDeletes;
use Just\Warehouse\Jobs\TransitionOrderStatus;

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
    use SoftDeletes,
        Concerns\HasOrderStatus,
        Concerns\ManagesPickList,
        Concerns\ManagesOrderStatus;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'created',
    ];

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

    /**
     * Mark the order as fulfilled.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function markAsFulfilled()
    {
        if (! $this->isValidTransition($this->status, 'fulfilled')) {
            throw new LogicException('This order can not be marked as fulfilled.');
        }

        OrderFulfilled::dispatch(tap($this)->update([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]));
    }

    /**
     * Process the order to be fulfilled.
     *
     * @return void
     */
    public function process()
    {
        TransitionOrderStatus::dispatch($this);
    }
}

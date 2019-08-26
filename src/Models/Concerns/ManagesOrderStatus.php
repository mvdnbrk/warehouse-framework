<?php

namespace Just\Warehouse\Models\Concerns;

use LogicException;
use Illuminate\Database\Eloquent\Builder;
use Just\Warehouse\Events\OrderFulfilled;
use Just\Warehouse\Jobs\TransitionOrderStatus;

trait ManagesOrderStatus
{
    /**
     * The possible transitions.
     *
     * @var array
     */
    private $transitions = [
        ['created' => 'open'],
        ['backorder' => 'open'],
        ['open' => 'backorder'],
        ['open' => 'fulfilled'],
        ['created' => 'backorder'],
    ];

    /**
     * Determine if a status transition is valid.
     *
     * @param  string  $oldStatus
     * @param  string  $newStatus
     * @return bool
     */
    public function isValidTransition($oldStatus, $newStatus)
    {
        if (! is_string($oldStatus) || ! is_string($newStatus) || $oldStatus === '' || $newStatus === '') {
            return false;
        }

        return collect($this->transitions)->contains($oldStatus, $newStatus);
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

    /**
     * Scope a query to only include backorders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBackorder(Builder $query)
    {
        return $query->where('status', 'backorder');
    }

    /**
     * Scope a query to only include open orders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen(Builder $query)
    {
        return $query->where('status', 'open');
    }
}

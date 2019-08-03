<?php

namespace Just\Warehouse\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

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

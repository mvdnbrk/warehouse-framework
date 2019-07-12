<?php

namespace Just\Warehouse\Models\Concerns;

use Just\Warehouse\Exceptions\InvalidStatusException;

/**
 * @property array $attributes
 */
trait HasOrderStatuses
{
    /**
     * Available order statuses.
     *
     * @var array
     */
    private $statuses = [
        'backorder',
        'created',
        'deleted',
        'open',
    ];

    /**
     * Determine if a status is valid.
     *
     * @param  string  $value
     * @return bool
     */
    public function isValidStatus($value)
    {
        return in_array($value, $this->statuses);
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
            $value = 'created';
        }

        if (! $this->isValidStatus($value)) {
            throw (new InvalidStatusException)->setModel(self::class, $value);
        }

        $this->attributes['status'] = $value;
    }
}

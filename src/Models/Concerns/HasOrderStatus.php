<?php

namespace Just\Warehouse\Models\Concerns;

use Just\Warehouse\Exceptions\InvalidStatusException;

/**
 * @property bool $exists
 * @property string $status
 * @property array $attributes
 */
trait HasOrderStatus
{
    /**
     * Available order statuses.
     *
     * @var array
     */
    private $statuses = [
        'open',
        'created',
        'deleted',
        'backorder',
        'fulfilled',
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
     * Get the status attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function getStatusAttribute($value)
    {
        return ! $this->exists ? 'draft' : $value;
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

    /**
     * Determine if the order is in "backorder".
     *
     * @return bool
     */
    public function isBackorder()
    {
        return $this->status === 'backorder';
    }

    /**
     * Determine if the order is in "created".
     *
     * @return bool
     */
    public function isCreated()
    {
        return $this->status === 'created';
    }

    /**
     * Determine if the order is "fulfilled".
     *
     * @return bool
     */
    public function isFulfilled()
    {
        return $this->status === 'fulfilled';
    }

    /**
     * Determine if the order is "open".
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->status === 'open';
    }
}

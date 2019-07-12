<?php

namespace Just\Warehouse\Exceptions;

use InvalidArgumentException;

class InvalidStatusException extends InvalidArgumentException
{
    /**
     * Name of the affected Eloquent model.
     *
     * @var string
     */
    protected $model;

    /**
     * The invalid status.
     *
     * @var string
     */
    protected $status;

    /**
     * Set the affected Eloquent model and status.
     *
     * @param  string  $model
     * @param  string  $status
     * @return $this
     */
    public function setModel($model, $status)
    {
        $this->model = $model;
        $this->status = $status;

        $this->message = "Invalid status '{$status}' for model [{$model}].";

        return $this;
    }

    /**
     * Get the affected Eloquent model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the invalid status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}

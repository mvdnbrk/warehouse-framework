<?php

namespace Just\Warehouse\Exceptions;

use Exception;

class ValidationException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('The given data was invalid.');
    }
}

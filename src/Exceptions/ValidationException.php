<?php

namespace Just\Warehouse\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct()
    {
        parent::__construct('The given data was invalid.');
    }
}

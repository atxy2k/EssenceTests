<?php

namespace App\Throwable;
use Exception;
class TodoCouldNotBeUpdatedException extends Exception
{

    public function __construct()
    {
        parent::__construct(__('Something was wrong when we trying to update the todo'));
    }
}

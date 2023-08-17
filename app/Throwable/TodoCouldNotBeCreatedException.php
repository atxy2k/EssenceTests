<?php

namespace App\Throwable;
use Exception;
class TodoCouldNotBeCreatedException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('Todo could not be created'));
    }
}

<?php

namespace App\Throwable;
use Exception;
class TodoNotFoundException extends Exception
{

    public function __construct()
    {
        parent::__construct(__('Todo not found'));
    }
}

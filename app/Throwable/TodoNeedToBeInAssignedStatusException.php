<?php namespace App\Throwable;

use Exception;
class TodoNeedToBeInAssignedStatusException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('Todo need to be assigned to continue'));
    }
}

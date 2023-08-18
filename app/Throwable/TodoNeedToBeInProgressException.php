<?php namespace App\Throwable;

use Exception;
class TodoNeedToBeInProgressException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('Todo need to be in progress to continue'));
    }
}

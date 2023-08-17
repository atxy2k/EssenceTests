<?php

namespace App\Repositories;

use App\Contracts\Repositories\TodosRepositoryInterface;
use App\Models\Todo;
use Atxy2k\Essence\Infrastructure\Repository;

class TodosRepository extends Repository implements TodosRepositoryInterface
{
    protected string|null $model = Todo::class;
}

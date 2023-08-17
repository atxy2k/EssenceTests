<?php

namespace App\Contracts\Services;

use App\Models\Todo;
use Atxy2k\Essence\Contracts\ServiceInterface;

interface TodosServiceInterface extends ServiceInterface
{
    public function create(array $data) : ?Todo;
    public function update(int $id, array $data) :bool;
    public function delete(int $id) : bool;
    public function assignResponsible(int $id, array $data) : bool;
    public function markAsInProgress(int $id) : bool;
    public function markAsCompleted(int $id) : bool;
}

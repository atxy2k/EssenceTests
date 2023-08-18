<?php

namespace App\Services;

use App\Contracts\Repositories\TodosRepositoryInterface;
use App\Contracts\Services\TodosServiceInterface;
use App\Enums\StatusTodoEnum;
use App\Models\Todo;
use App\Throwable\TodoCouldNotBeCreatedException;
use App\Throwable\TodoCouldNotBeUpdatedException;
use App\Throwable\TodoNeedToBeInProgressException;
use App\Throwable\TodoNotFoundException;
use App\Throwable\ValidationException;
use App\Validators\TodosValidator;
use Atxy2k\Essence\Infrastructure\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\Auth;
class TodosService extends Service implements TodosServiceInterface
{

    public function __construct(private readonly TodosRepositoryInterface $todosRepository, TodosValidator $todosValidator)
    {
        parent::__construct();
        $this->validator = $todosValidator;
    }

    public function create(array $data) : ?Todo
    {
        $return = null;
        try
        {
            DB::beginTransaction();
            throw_unless($this->validator->with($data)->passes(TodosValidator::CREATE),
                new ValidationException($this->validator->errors()->first()));
            $data['description'] = Arr::get($data,'description','');
            $data['created_by_id'] = Auth::id();
            $data['status'] = StatusTodoEnum::Waiting;
            $item = $this->todosRepository->create($data);
            throw_if(is_null($item), TodoCouldNotBeCreatedException::class);
            DB::commit();
            $return = $item;
        }
        catch (Throwable $e)
        {
            $this->pushError($e->getMessage());
            DB::rollBack();
        }
        return $return;
    }


    public function update(int $id, array $data): bool
    {
        $return = false;
        try
        {
            DB::beginTransaction();
            throw_unless($this->validator->with($data)->passes(TodosValidator::CHANGE),
                new ValidationException($this->validator->errors()->first()));
            $item = $this->todosRepository->find($id);
            throw_if(is_null($item), TodoNotFoundException::class);
            $data['updated_by_id'] = Auth::id();
            throw_unless($this->todosRepository->update($id, $data), TodoCouldNotBeUpdatedException::class);
            DB::commit();
            $return = true;
        }
        catch (Throwable $e)
        {
            DB::rollBack();
            $this->pushError($e->getMessage());
        }
        return $return;
    }

    public function delete(int $id): bool
    {
        $return = false;
        try
        {
            DB::beginTransaction();
            $item = $this->todosRepository->find($id);
            throw_if(is_null($item),TodoNotFoundException::class);
            $item->delete();
            DB::commit();
            $return = true;
        }
        catch (Throwable $e)
        {
            DB::rollBack();
            $this->pushError($e->getMessage());
        }
        return $return;
    }

    public function assignResponsible(int $id, array $data): bool
    {
        $return = false;
        try
        {
            DB::beginTransaction();
            throw_unless($this->validator->with($data)->passes(TodosValidator::ASSIGN_RESPONSIBLE),
                new ValidationException($this->validator->errors()->first()));
            $item = $this->todosRepository->find($id);
            throw_if(is_null($item), TodoNotFoundException::class);
            $data['updated_by_id'] = Auth::id();
            $data['responsible_id'] = Arr::get($data, 'responsible_id');
            $data['status'] = StatusTodoEnum::Assigned;
            throw_unless($this->todosRepository->update($id, $data), TodoCouldNotBeUpdatedException::class);
            DB::commit();
            $return = true;
        }
        catch (Throwable $e)
        {
            DB::rollBack();
            $this->pushError($e->getMessage());
        }
        return $return;
    }

    public function markAsInProgress(int $id): bool
    {
        $return = false;
        try
        {
            DB::beginTransaction();
            $item = $this->todosRepository->find($id);
            throw_if($item->status !== StatusTodoEnum::Assigned,
                TodoNeedToBeInProgressException::class);
            throw_if(is_null($item), TodoNotFoundException::class);
            $item->update([
                'updated_by_id' => Auth::id(),
                'status' => StatusTodoEnum::InProgress
            ]);
            DB::commit();
            $return = true;
        }
        catch (Throwable $e)
        {
            DB::rollBack();
            $this->pushError($e->getMessage());
        }
        return $return;
    }

    public function markAsCompleted(int $id): bool
    {
        $return = false;
        try
        {
            DB::beginTransaction();
            $item = $this->todosRepository->find($id);
            throw_unless($item->status === StatusTodoEnum::InProgress,
                TodoNeedToBeInProgressException::class);
            throw_if(is_null($item), TodoNotFoundException::class);
            $item->update([
                'updated_by_id' => Auth::id(),
                'status' => StatusTodoEnum::Completed
            ]);
            DB::commit();
            $return = true;
        }
        catch (Throwable $e)
        {
            DB::rollBack();
            $this->pushError($e->getMessage());
        }
        return $return;
    }


}

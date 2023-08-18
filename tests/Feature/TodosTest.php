<?php namespace Tests\Feature;

use App\Contracts\Repositories\TodosRepositoryInterface;
use App\Contracts\Services\TodosServiceInterface;
use App\Enums\StatusTodoEnum;
use App\Models\User;
use App\Repositories\TodosRepository;
use App\Services\TodosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TodosTest extends TestCase
{
    use RefreshDatabase;
    public function test_create_with_wrong_data_return_null()
    {
        $service = $this->app->make(TodosServiceInterface::class);
        $data = [];
        $this->assertNull($service->create($data));
        $this->assertNotNull($service->errors());
        $this->assertNotNull($service->errors()->first());
    }

    public function test_create_with_data_but_without_user_saved_successfully(){
        $service = $this->app->make(TodosServiceInterface::class);
        $data = [
            'title' => 'This is a test task'
        ];
        $item = $service->create($data);
        $this->assertNotNull($item, $service->errors()->first());
        $this->assertNotNull($item->title);
        $this->assertNotNull($item->description);
        $this->assertNull($item->created_by_id);
        $this->assertNull($item->updated_by_id);
        $this->assertNull($item->responsible_id);
        $this->assertNotNull($item->status);
        $this->assertNotNull($item->status);
        $this->assertEquals($item->status, StatusTodoEnum::Waiting);
    }

    public function test_create_with_user_data_saved_successfully(){
        $user = User::create([
            'email' => sprintf('%s@gmail.com', strtolower(Str::random(8))),
            'name' => Str::random(10),
            'password' => Hash::make(Str::random(9))
        ]);
        $this->assertNotNull($user);
        Auth::login($user);
        $data = [
            'title' => Str::random(10),
            'description' => Str::random(20)
        ];
        $service = $this->app->make(TodosServiceInterface::class);
        $item = $service->create($data);
        $this->assertNotNull($item);
        $this->assertEquals($data['title'], $item->title);
        $this->assertEquals($data['description'], $item->description);
        $this->assertEquals($user->id, $item->created_by_id);
        $this->assertNotNull($item->id);
        $this->assertNotNull($item->created_by);
        $this->assertInstanceOf(User::class, $item->created_by);
        $this->assertNull($item->updated_by_id);
        $this->assertNull($item->responsible_id);
        $this->assertNull($item->updated_by);
        $this->assertNull($item->responsible);
    }

    public function test_update_with_wrong_params_return_false()
    {
        $service = $this->app->make(TodosServiceInterface::class);
        $data = [
            'title' => 'This is a test task'
        ];
        $item = $service->create($data);
        $this->assertNotNull($item, $service->errors()->first());
        $this->assertFalse($service->update($item->id, []));
        $this->assertNotNull($service->errors());
        $this->assertNotNull($service->errors()->first());
        $this->assertTrue($service->errors()->count() > 0);
    }

    public function test_change_with_user_data_completed_successfully(){
        $user = User::create([
            'email' => sprintf('%s@gmail.com', strtolower(Str::random(8))),
            'name' => Str::random(10),
            'password' => Hash::make(Str::random(9))
        ]);
        $second_user = User::create([
            'email' => sprintf('%s@gmail.com', strtolower(Str::random(8))),
            'name' => Str::random(10),
            'password' => Hash::make(Str::random(9))
        ]);
        $this->assertNotNull($user);
        $this->assertNotNull($second_user);
        Auth::login($user);
        $data = [
            'title' => Str::random(10),
            'description' => Str::random(20)
        ];
        $service = $this->app->make(TodosServiceInterface::class);
        $item = $service->create($data);
        $this->assertNotNull($item, $service->errors()->first());
        $updated_data = [
            'title' => Str::random(10),
            'description' => Str::random(20)
        ];
        Auth::login($second_user);
        $response = $service->update($item->id, $updated_data);
        $this->assertTrue($response);
        $repository = $this->app->make(TodosRepositoryInterface::class);
        $this->assertNotNull($repository);
        $updated_item = $repository->find($item->id);
        $this->assertNotNull($updated_item);
        $this->assertEquals($updated_item->id, $item->id);
        $this->assertEquals($updated_data['title'], $updated_item->title);
        $this->assertEquals($updated_data['description'], $updated_item->description);
        $this->assertEquals($user->id, $updated_item->created_by_id);
        $this->assertNotNull($updated_item->updated_by_id);
        $this->assertEquals($second_user->id, $updated_item->updated_by_id);
        $this->assertNotNull($updated_item->updated_by);
        $this->assertInstanceOf(User::class, $updated_item->updated_by);
        $this->assertNull($updated_item->responsible_id);
        $this->assertEquals($updated_item->status, StatusTodoEnum::Waiting);
    }

    public function test_delete_with_wrong_data_return_false()
    {
        $service = $this->app->make(TodosServiceInterface::class);
        $this->assertFalse($service->delete(-1));
        $this->assertNotNull($service->errors());
        $this->assertNotNull($service->errors()->first());
        $this->assertTrue($service->errors()->count() > 0);
    }

    public function test_delete_with_data_but_without_user_deleted_successfully(){
        $service = $this->app->make(TodosServiceInterface::class);
        $data = [
            'title' => 'This is a test task'
        ];
        $item = $service->create($data);
        $this->assertNotNull($item, $service->errors()->first());
        $response = $service->delete($item->id);
        $this->assertTrue($response);
        $repository = $this->app->make(TodosRepositoryInterface::class);
        $deleted = $repository->find($item->id);
        $this->assertNull($deleted);
    }

    public function test_assign_responsible_to_todo()
    {
        $user = User::create([
            'email' => sprintf('%s@gmail.com', strtolower(Str::random(8))),
            'name' => Str::random(10),
            'password' => Hash::make(Str::random(9))
        ]);
        $this->assertNotNull($user);
        $second_user = User::create([
            'email' => sprintf('%s@gmail.com', strtolower(Str::random(8))),
            'name' => Str::random(10),
            'password' => Hash::make(Str::random(9))
        ]);
        $this->assertNotNull($second_user);

        Auth::login($user);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(20)
        ];
        $service = $this->app->make(TodosServiceInterface::class);
        $item = $service->create($data);
        $this->assertNotNull($item);

        $responsible_data = [
            'responsible_id' => $second_user->id
        ];
        $response = $service->assignResponsible($item->id, $responsible_data);
        $this->assertTrue($response);
        $this->assertEquals($data['title'], $item->title);
        $this->assertEquals($data['description'], $item->description);

        $repository = $this->app->make(TodosRepositoryInterface::class);
        $item_updated = $repository->find($item->id);
        $this->assertNotNull($item_updated);
        $this->assertEquals($item_updated->created_by_id, $user->id);
        $this->assertEquals($item_updated->updated_by_id, $user->id);
        $this->assertEquals($item_updated->responsible_id, $second_user->id);
        $this->assertEquals($item_updated->status, StatusTodoEnum::Assigned);
    }
    public function test_mark_as_completed_return_true()
    {
        $user = User::create([
            'email' => sprintf('%s@gmail.com', strtolower(Str::random(8))),
            'name' => Str::random(10),
            'password' => Hash::make(Str::random(9))
        ]);
        $this->assertNotNull($user);
        $second_user = User::create([
            'email' => sprintf('%s@gmail.com', strtolower(Str::random(8))),
            'name' => Str::random(10),
            'password' => Hash::make(Str::random(9))
        ]);
        $this->assertNotNull($second_user);

        Auth::login($user);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(20)
        ];
        $service = $this->app->make(TodosServiceInterface::class);
        $item = $service->create($data);
        $this->assertNotNull($item);

        $responsible_data = [
            'responsible_id' => $second_user->id
        ];
        $response = $service->assignResponsible($item->id, $responsible_data);
        $this->assertTrue($response);
        $this->assertEquals($data['title'], $item->title);

        $this->assertTrue($service->markAsInProgress($item->id));

        $completed = $service->markAsCompleted($item->id);
        $this->assertTrue($completed, $service->errors()->first());

        $repository = $this->app->make(TodosRepositoryInterface::class);
        $this->assertNotNull($repository);
        $item_completed = $repository->find($item->id);
        $this->assertEquals($item_completed->status, StatusTodoEnum::Completed);

    }



}

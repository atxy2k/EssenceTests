<?php namespace Tests\Feature;

use App\Contracts\Services\TodosServiceInterface;
use App\Enums\StatusTodoEnum;
use App\Models\User;
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

}

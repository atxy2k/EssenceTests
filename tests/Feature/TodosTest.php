<?php namespace Tests\Feature;

use App\Contracts\Services\TodosServiceInterface;
use App\Enums\StatusTodoEnum;
use App\Services\TodosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

}

<?php

namespace App\Providers;

use App\Contracts\Repositories\TodosRepositoryInterface;
use App\Contracts\Services\TodosServiceInterface;
use App\Repositories\TodosRepository;
use App\Services\TodosService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TodosRepositoryInterface::class, TodosRepository::class);
        $this->app->bind(TodosServiceInterface::class,TodosService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

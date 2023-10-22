<?php

namespace App\Providers;

use App\Utilities\Contracts\RedisHelperInterface;
use App\Utilities\Services\RedisStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RedisHelperInterface::class, RedisStore::class);


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

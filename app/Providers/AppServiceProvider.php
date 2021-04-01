<?php

namespace App\Providers;

use Illuminate\Testing\TestResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\Assert as PHPUnit;

use function PHPUnit\Framework\lessThanOrEqual;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TestResponse::macro('assertJsonCountLessOrEqualTo', function (int $count, $key = null) {
            return PHPUnit::assertThat(
                count($key), lessThanOrEqual($count), 
                "Failed to assert that the response count is less or equal to {$count}"
            );
        });
    }
}

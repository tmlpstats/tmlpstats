<?php namespace TmlpStats\Providers;

///////////////////////////////
// THIS CODE IS AUTO-GENERATED
// do not edit this code by hand!
//
// To edit the resulting API code, instead edit config/reports.yml
// and then run the command:
//   php artisan reports:codegen
use Illuminate\Support\ServiceProvider;
use TmlpStats\Api;

class ApiProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return  void
     */
    public function register()
    {
        $this->app->singleton(Api\GlobalReport::class);
        $this->app->singleton(Api\LocalReport::class);
    }
}

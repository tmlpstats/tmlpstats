<?php
namespace TmlpStats\Providers;

use Blade;
use Session;
use Illuminate\Support\ServiceProvider;
use TmlpStats\Util;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Illuminate\Contracts\Auth\Registrar::class,
            \TmlpStats\Services\Registrar::class
        );

        Blade::directive('date', function($expression) {
            $format = Util::getLocaleDateFormat();

            return "<?php echo with{$expression}->format('{$format}'); ?>";
        });
    }

}

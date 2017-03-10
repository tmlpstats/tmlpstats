<?php
namespace TmlpStats\Providers;

use Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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

        Blade::directive('date', function ($expression) {
            $php = "<?php echo with{$expression}->toDateString(); ?>";

            return "<span class='date' data-date='{$php}'></span>";
        });

        Blade::directive('statsReportLink', function ($expression) {
            // Remove wrapping parens
            $data = substr($expression, 1, -1);
            $query = '';
            if (strpos($data, ',') !== false) {
                $parts = explode(',', $data);
                $data = $parts[0];
                $query = trim(str_replace("'", '', $parts[1]));
                $query = trim(str_replace('"', '', $query));
            }

            $href = "<?php echo \\TmlpStats\\Http\\Controllers\\StatsReportController::getUrl({$data}) . '{$query}'; ?>";

            // Build php scripts. Have to break it down since {{ }} notation creates additional php tags
            $returnPhp = "<?php \$condition = Gate::allows('read', {$data}); ?>";
            $returnPhp .= '<?php if ($condition): ?>';
            $returnPhp .= "<a href='{$href}'>";
            $returnPhp .= '<?php endif; ?>';

            return $returnPhp;
        });

        Blade::directive('endStatsReportLink', function () {

            $tag = "'</a>'";

            return "<?php if (\$condition) { echo {$tag}; } ?>";
        });

        // Using @json($foo) works out to the equivalent of {!! json_encode($foo) !!}
        Blade::directive('json', function ($expression) {
            return "<?php echo json_encode{$expression} ?>";
        });

        Blade::directive('json_pretty', function ($expression) {
            $expression = substr($expression, 1, -1);
            return "<?php echo json_encode({$expression}, JSON_PRETTY_PRINT) ?>";
        });
    }

}

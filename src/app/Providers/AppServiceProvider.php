<?php
namespace TmlpStats\Providers;

use Blade;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use TmlpStats\Api;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') === 'local') {
            \DB::connection()->enableQueryLog();
            \Event::listen('kernel.handled', function ($request, $response) {
                if ($request->has('sql-debug')) {
                    $output = [];
                    foreach (\DB::getQueryLog() as $query) {
                        foreach ($query['bindings'] as $i => $binding) {
                            if ($binding instanceOf Carbon) {
                                $query['bindings'][$i] = $binding->toDateString();
                            }
                        }

                        $output["{$query['time']}"][] = str_replace(['?'], $query['bindings'], $query['query']);
                    }
                    ksort($output);

                    file_put_contents(
                        storage_path("app/sql.log"),
                        print_r($output, true),
                        FILE_APPEND
                    );
                }
            });
        }
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
        $this->app->singleton(Api\Context::class);

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

            $returnPhp = '';
            // Build php scripts. Have to break it down since {{ }} notation creates additional php tags
            /* $returnPhp = "<?php \$condition = Gate::allows('read', {$data}); ?>";*/
            /* $returnPhp .= '<?php if ($condition): ?>';*/
            $returnPhp .= "<a href='{$href}'>";
            /* $returnPhp .= '<?php endif; ?>';*/

            return $returnPhp;
        });

        Blade::directive('endStatsReportLink', function () {
            return '</a>';

            /* $tag = "'</a>'";
            return "<?php if (\$condition) { echo {$tag}; } ?>";*/
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

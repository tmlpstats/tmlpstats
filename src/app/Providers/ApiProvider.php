<?php
namespace TmlpStats\Providers;

///////////////////////////////
// THIS CODE IS AUTO-GENERATED
// do not edit this code by hand!
//
// To edit the resulting API code, instead edit config/reports.yml
// and then run the command:
//   php artisan reports:codegen
//
///////////////////////////////

use Illuminate\Support\ServiceProvider;
use TmlpStats\Api;

class ApiProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return  void
     */
    public function register()
    {
        $this->app->singleton(Api\Admin\Region::class);
        $this->app->singleton(Api\Admin\Quarter::class);
        $this->app->singleton(Api\Application::class);
        $this->app->singleton(Api\Context::class);
        $this->app->singleton(Api\Course::class);
        $this->app->singleton(Api\GlobalReport::class);
        $this->app->singleton(Api\LiveScoreboard::class);
        $this->app->singleton(Api\LocalReport::class);
        $this->app->singleton(Api\Scoreboard::class);
        $this->app->singleton(Api\Submission\NextQtrAccountability::class);
        $this->app->singleton(Api\SubmissionCore::class);
        $this->app->singleton(Api\SubmissionData::class);
        $this->app->singleton(Api\TeamMember::class);
        $this->app->singleton(Api\UserProfile::class);
        $this->app->singleton(Api\ValidationData::class);
    }

    public function provides()
    {
        return [
            'TmlpStats\Api\Admin\Region',
            'TmlpStats\Api\Admin\Quarter',
            'TmlpStats\Api\Application',
            'TmlpStats\Api\Context',
            'TmlpStats\Api\Course',
            'TmlpStats\Api\GlobalReport',
            'TmlpStats\Api\LiveScoreboard',
            'TmlpStats\Api\LocalReport',
            'TmlpStats\Api\Scoreboard',
            'TmlpStats\Api\Submission\NextQtrAccountability',
            'TmlpStats\Api\SubmissionCore',
            'TmlpStats\Api\SubmissionData',
            'TmlpStats\Api\TeamMember',
            'TmlpStats\Api\UserProfile',
            'TmlpStats\Api\ValidationData',
        ];
    }
}

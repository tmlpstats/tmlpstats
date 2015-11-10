<?php
namespace TmlpStats\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use TmlpStats\Center;
use TmlpStats\GlobalReport;
use TmlpStats\Policies\CenterPolicy;
use TmlpStats\Policies\GlobalReportPolicy;
use TmlpStats\Policies\ReportTokenPolicy;
use TmlpStats\Policies\StatsReportPolicy;
use TmlpStats\Policies\UserPolicy;
use TmlpStats\ReportToken;
use TmlpStats\StatsReport;
use TmlpStats\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Center::class       => CenterPolicy::class,
        GlobalReport::class => GlobalReportPolicy::class,
        ReportToken::class  => ReportTokenPolicy::class,
        StatsReport::class  => StatsReportPolicy::class,
        User::class         => UserPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        //
    }
}

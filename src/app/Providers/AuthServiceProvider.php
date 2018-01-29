<?php
namespace TmlpStats\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use TmlpStats as Models;
use TmlpStats\Policies;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Models\Center::class       => Policies\CenterPolicy::class,
        Models\GlobalReport::class => Policies\GlobalReportPolicy::class,
        Models\HelpVideo::class    => Policies\HelpVideoPolicy::class,
        Models\Invite::class       => Policies\InvitePolicy::class,
        Models\Region::class       => Policies\RegionPolicy::class,
        Models\ReportToken::class  => Policies\ReportTokenPolicy::class,
        Models\StatsReport::class  => Policies\StatsReportPolicy::class,
        Models\User::class         => Policies\UserPolicy::class,
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

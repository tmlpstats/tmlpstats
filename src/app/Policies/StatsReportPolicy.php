<?php

namespace TmlpStats\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use TmlpStats\StatsReport;
use TmlpStats\User;

class StatsReportPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function before($user, $ability)
    {
        if ($user->hasRole('administrator')) {
            return true;
        }
    }

    public function create(User $user)
    {
        return false;
    }

    public function read(User $user, StatsReport $statsReport)
    {
        return ($user->hasRole('globalStatistician')
            || ($user->hasRole('localStatistician') && $user->center->id === $statsReport->center->id))
            || ($user->hasRole('localStatistician') && $user->id === $statsReport->user->id);
    }

    public function update(User $user, StatsReport $statsReport)
    {
        return ($user->hasRole('globalStatistician')
            || ($user->hasRole('localStatistician') && $user->center->id === $statsReport->center->id));
    }

    public function delete(User $user, StatsReport $statsReport)
    {
        return $user->hasRole('globalStatistician');
    }

    public function submit(User $user, StatsReport $statsReport)
    {
        // Let's allow teams to submit stats for eachother for now. teamwork!
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician');
    }

    public function downloadSheet(User $user, StatsReport $statsReport)
    {
        return $this->read($user, $statsReport);
    }

    public function index(User $user)
    {
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician');
    }

    public function validate(User $user)
    {
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician');
    }
}

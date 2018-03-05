<?php
namespace TmlpStats\Policies;

use TmlpStats\StatsReport;
use TmlpStats\User;

class StatsReportPolicy extends Policy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Can $user view $statsReport?
     *
     * @param User $user
     * @param StatsReport $statsReport
     * @return bool
     */
    public function read(User $user, StatsReport $statsReport)
    {
        if ($user->hasRole('globalStatistician') || $user->hasRole('programLeader')) {
            return true;
        } else if ($user->hasRole('localStatistician')) {
            return $user->center->id === $statsReport->center->id || $user->id === $statsReport->user->id;
        } else if ($user->hasRole('readonly')) {
            return $user->center && $user->center->id === $statsReport->center->id;
        }

        return false;
    }

    /**
     * Can $user update $statsReport?
     *
     * @param User $user
     * @param StatsReport $statsReport
     * @return bool
     */
    public function update(User $user, StatsReport $statsReport)
    {
        return ($user->hasRole('globalStatistician')
            || ($user->hasRole('localStatistician') && $user->center->id === $statsReport->center->id));
    }

    /**
     * Can $user delete $statsReport?
     *
     * @param User $user
     * @param StatsReport $statsReport
     * @return bool
     */
    public function delete(User $user, StatsReport $statsReport)
    {
        return $user->hasRole('globalStatistician');
    }

    /**
     * Can $user view the full list of statsReports?
     *
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician') || $user->hasRole('programLeader');
    }

    /**
     * Can $user view the contact info for $statsReport
     *
     * @param User $user
     * @param StatsReport $statsReport
     * @return bool
     */
    public function readContactInfo(User $user, StatsReport $statsReport)
    {
        return $this->read($user, $statsReport) && !$user->hasRole('readonly');
    }

    public function showReportButton(User $user)
    {
        if ($user->hasRole('readonly')) {
            return ($user->reportToken && $user->reportToken->hasOwner());
        }

        return true;
    }

    public function showReportNavLinks(User $user)
    {
        return $user->hasRole('globalStatistician');
    }
}

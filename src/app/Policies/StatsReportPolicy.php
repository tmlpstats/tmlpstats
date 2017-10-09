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
            $result = $user->center && $user->center->id === $statsReport->center->id;

            return $result;
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
     * Can $user submit a stats report?
     *
     * @param User $user
     * @param StatsReport $statsReport
     * @return bool
     */
    public function submit(User $user, StatsReport $statsReport)
    {
        // Let's allow teams to submit stats for eachother for now. teamwork!
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician');
    }

    /**
     * Can $user download the spreadsheet from $statsReport?
     *
     * @param User $user
     * @param StatsReport $statsReport
     * @return bool
     */
    public function downloadSheet(User $user, StatsReport $statsReport)
    {
        // No downloading for readonly users
        return $this->read($user, $statsReport) && !$user->hasRole('readonly');
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
     * Can $user validate spreadsheets?
     *
     * @param User $user
     * @return bool
     */
    public function validate(User $user)
    {
        return $user->hasRole('globalStatistician') || $user->hasRole('localStatistician');
    }

    /**
     * Can $user import spreadsheets (distinct from submitting)
     *
     * @param User $user
     * @return bool
     */
    public function import(User $user)
    {
        return $user->hasRole('globalStatistician');
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

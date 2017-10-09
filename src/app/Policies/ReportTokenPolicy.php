<?php
namespace TmlpStats\Policies;

use TmlpStats\User;

class ReportTokenPolicy extends Policy
{
    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Can $user view the full list of reportTokens?
     *
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return false; // admin only
    }

    /**
     * Can user view the ReportToken's URL?
     *
     * @param User $user
     * @return bool
     */
    public function readLink(User $user)
    {
        return ($user->hasRole('globalStatistician') || $user->hasRole('localStatistician') || $user->hasRole('programLeader'));
    }
}

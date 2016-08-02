<?php

namespace TmlpStats\Policies;

class CenterPolicy extends Policy
{
    /**
     * Can $user view new submission UI?
     *
     * @param User $user
     * @param Center $center
     * @return bool
     */
    public function viewSubmissionUi(User $user, Center $center)
    {
        if ($user->hasRole('administrator')) {
            return true;
        }

        return false;
    }

    /**
     * Can $user make a submission?
     *
     * @param User $user
     * @param Center $center
     * @return bool
     */
    public function submitStats(User $user, Center $center)
    {
        if ($user->hasRole('globalStatistician')) {
            return true;
        }
        if ($user->hasRole('localStatistician')) {
            return ($user->person->centerId == $center->id);
        }

        return false;
    }
}

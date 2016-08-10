<?php

namespace TmlpStats\Policies;

use TmlpStats\Center;
use TmlpStats\User;

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
        // administrators are handled by "before" in the base policy
        return ($user->hasRole('globalStatistician'));
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

    public function adminScoreboard(User $user, Center $center)
    {
        return ($user->hasRole('globalStatistician'));
    }
}

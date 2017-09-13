<?php

namespace TmlpStats\Policies;

use TmlpStats\Api;
use TmlpStats\Center;
use TmlpStats\User;

class CenterPolicy extends Policy
{
    public function showNewSubmissionUi(User $user, Center $center)
    {
        $setting = Api\Context::ensure()->getSetting('showNewSubmissionUi', $center);
        if ($setting !== null) {
            return (bool) $setting;
        }

        return false; // Only used to trigger the link, and only global statisticians will have it.
    }

    /**
     * Can $user view new submission UI?
     *
     * @param User $user
     * @param Center $center
     * @return bool
     */
    public function viewSubmissionUi(User $user, Center $center)
    {
        // currently identical to submitStats
        return $this->submitStats($user, $center);
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

    public function submitOldStats(User $user, Center $center)
    {
        return $user->hasRole('globalStatistician');
    }

    public function skipSubmitEmail(User $user, Center $center)
    {
        return $user->hasRole('globalStatistician');
    }

    public function adminScoreboard(User $user, Center $center)
    {
        return ($user->hasRole('globalStatistician'));
    }

    public function copyQuarterData(User $user, Center $center)
    {
        return $user->hasRole('globalStatistician');
    }

    public function overrideDelete(User $user, Center $center)
    {
        return $user->hasRole('globalStatistician');
    }
}

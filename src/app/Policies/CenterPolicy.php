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
}

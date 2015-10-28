<?php namespace TmlpStats\Reports\Arrangements;

/*
 * Builds an array of GITW effectivness for each team member
 */

class GitwByTeamMember extends TeamMemberWeeklyValue
{
    public function getValue($data)
    {
        return $data->gitw;
    }
}

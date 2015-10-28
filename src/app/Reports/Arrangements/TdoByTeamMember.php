<?php namespace TmlpStats\Reports\Arrangements;

/*
 * Builds an array of TDO attendance for each team member
 */

class TdoByTeamMember extends TeamMemberWeeklyValue
{
    public function getValue($data)
    {
        return $data->tdo;
    }
}

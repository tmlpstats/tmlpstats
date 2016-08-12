<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Models a team application
 */
class TeamMember extends ParserDomain
{
    protected static $validProperties = [
        'id' => [
            'owner' => 'teamMember',
            'type' => 'int',
        ],
        'firstName' => [
            'owner' => 'person',
            'type' => 'string',
        ],
        'lastName' => [
            'owner' => 'person',
            'type' => 'string',
        ],
        'phone' => [
            'owner' => 'person',
            'type' => 'string',
        ],
        'email' => [
            'owner' => 'person',
            'type' => 'string',
        ],
        'center' => [
            'owner' => 'person',
            'type' => 'Center',
            'assignId' => true,
        ],
        'teamYear' => [
            'owner' => 'teamMember',
            'type' => 'int',
        ],
        'incomingQuarter' => [
            'owner' => 'teamMember',
            'type' => 'Quarter',
            'assignId' => true,
        ],
        'isReviewer' => [
            'owner' => 'teamMember',
            'type' => 'bool',
        ],
        'atWeekend' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'xferIn' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'xferOut' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'ctw' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'rereg' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'except' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'travel' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'room' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'gitw' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'tdo' => [
            'owner' => 'teamMemberData',
            'type' => 'bool',
        ],
        'withdrawCode' => [
            'owner' => 'applicationData',
            'type' => 'WithdrawCode',
            'assignId' => true,
        ],
        'comment' => [
            'owner' => 'applicationData',
            'type' => 'string',
        ],
    ];

    public static function fromModel($teamMemberData, $teamMember = null, $person = null)
    {
        if ($teamMember === null) {
            $teamMember = $teamMemberData->teamMember;
        }
        if ($person === null) {
            $person = $teamMember->person;
        }

        $obj = new static();
        foreach (static::$validProperties as $k => $v) {
            switch ($v['owner']) {
                case 'person':
                    $obj->$k = $person->$k;
                    break;
                case 'teamMember':
                    $obj->$k = $teamMember->$k;
                    break;
                case 'teamMemberData':
                    if ($teamMemberData) {
                        $obj->$k = $teamMemberData->$k;
                    }
            }
        }

        return $obj;
    }

    public function fillModel($teamMemberData, $teamMember = null, $only_set = true)
    {
        if ($teamMember === null) {
            $teamMember = $teamMemberData->teamMember;
        }

        foreach ($this->_values as $k => $v) {
            if ($only_set && !array_key_exists($k, $this->_setValues)) {
                continue;
            }
            $conf = self::$validProperties[$k];
            switch ($conf['owner']) {
                case 'person':
                    $target = $teamMember->person;
                    break;
                case 'teamMember':
                    $target = $teamMember;
                    break;
                case 'teamMemberData':
                    $target = $teamMemberData;
                    break;
            }
            if ($target !== null) {
                $this->copyTarget($target, $k, $v, $conf);
            }
        }
    }

}

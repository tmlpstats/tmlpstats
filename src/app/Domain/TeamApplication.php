<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Models a team application
 */
class TeamApplication extends ParserDomain
{
    protected static $validProperties = [
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
        'unsubscribed' => [
            'owner' => 'person',
            'type' => 'bool',
        ],
        'teamYear' => [
            'owner' => 'application',
            'type' => 'int',
        ],
        'regDate' => [
            'owner' => 'application',
            'type' => 'date',
        ],
        'isReviewer' => [
            'owner' => 'application',
            'type' => 'bool',
        ],
        'tmlpRegistrationId' => [
            'owner' => 'applicationData',
            'type' => 'int',
        ],
        'appOutDate' => [
            'owner' => 'applicationData',
            'type' => 'date',
        ],
        'appInDate' => [
            'owner' => 'applicationData',
            'type' => 'date',
        ],
        'apprDate' => [
            'owner' => 'applicationData',
            'type' => 'date',
        ],
        'wdDate' => [
            'owner' => 'applicationData',
            'type' => 'date',
        ], /*  TODO
        'withdrawCode' => [
            'owner' => 'applicationData',
            'type' => 'WithdrawCode',
        ], */
        'committedTeamMemberId' => [
            'owner' => 'applicationData',
            'type' => 'int',
        ],
        'incomingQuarter' => [
            'owner' => 'applicationData',
            'type' => 'Quarter',
            'assignId' => true,
        ],
        'comment' => [
            'owner' => 'applicationData',
            'type' => 'string',
        ],
        'travel' => [
            'owner' => 'applicationData',
            'type' => 'bool',
        ],
        'room' => [
            'owner' => 'applicationData',
            'type' => 'bool',
        ],
    ];

    public static function fromModel($appData, $application = null, $person = null)
    {
        if ($application === null) {
            $application = $appData->registration;
        }
        if ($person === null) {
            $person = $application->person;
        }

        $obj = new static();
        foreach (static::$validProperties as $k => $v) {
            switch ($v['owner']) {
                case 'person':
                    $obj->$k = $person->$k;
                    break;
                case 'application':
                    $obj->$k = $application->$k;
                    break;
                case 'applicationData':
                    $obj->$k = $appData->$k;
            }
        }
        $obj->tmlpRegistrationId = $application->id;

        return $obj;
    }

    public function fillModel($appData, $application = null, $only_set = true)
    {
        if ($application === null) {
            $application = $appData->registration;
        }

        foreach ($this->_values as $k => $v) {
            if ($only_set && (!isset($this->_setValues[$k]) || !$this->_setValues[$k])) {
                continue;
            }
            $conf = self::$validProperties[$k];
            switch ($conf['owner']) {
                case 'person':
                    $target = $application->person;
                    break;
                case 'application':
                    $target = $application;
                    break;
                case 'applicationData':
                    $target = $appData;
                    break;
            }
            if ($k == 'regDate') {
                $appData->regDate = $v;
            }
            $this->copyTarget($target, $k, $v, $conf);
        }
    }
}

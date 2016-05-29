<?php
namespace TmlpStats\Domain;

use Illuminate\Contracts\Support\Arrayable;
use TmlpStats as Models;

/**
 * Models a team application
 */
class TeamApplication extends ParserDomain implements Arrayable, \JsonSerializable
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
        ],
        'withdrawCode' => [
            'owner' => 'applicationData',
            'type' => 'WithdrawCode',
        ],
        'committedTeamMember' => [
            'owner' => 'applicationData',
            'type' => 'TeamMember',
        ],
        'incomingQuarter' => [
            'owner' => 'applicationData',
            'type' => 'Quarter',
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

    public function __construct()
    {
        // do nothing!
    }

    public function toArray()
    {
        $vprops = static::$validProperties;
        foreach ($this->values as $k => $v) {
            if ($v !== null) {
                if ($vprops[$k]['type'] == 'date') {
                    $v = $v->toDateString();
                }
            }
            $a[$k] = $v;
        }

        return $a;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

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

        foreach ($this->values as $k => $v) {
            if ($only_set && (!isset($this->setValues[$k]) || !$this->setValues[$k])) {
                continue;
            }
            switch (self::$validProperties[$k]['owner']) {
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
            $this->copyTarget($target, $k, $v);
        }
    }
}

<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Models a team application
 */
class TeamApplication extends ParserDomain
{
    public $meta = [];

    protected static $validProperties = [
        'firstName' => [
            'owner' => 'person',
            'type' => 'string',
            'options' => ['trim' => true],
        ],
        'lastName' => [
            'owner' => 'person',
            'type' => 'string',
            'options' => ['trim' => true],
        ],
        'email' => [
            'owner' => 'person',
            'type' => 'string',
            'options' => ['trim' => true],
        ],
        'center' => [
            'owner' => 'person',
            'type' => 'Center',
            'assignId' => true,
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
        'id' => [
            'owner' => 'application',
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
            'assignId' => true,
        ],
        'committedTeamMember' => [
            'owner' => 'applicationData',
            'type' => 'TeamMember',
            'assignId' => true,
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
        'phone' => [
            'owner' => 'person',
            'type' => 'string',
            'options' => ['trim' => true],
        ],
        '_personId' => [
            'owner' => 'application',
            'type' => 'int',
            'domainOnly' => true,
        ],
        /*
        FIELDS WE CURRENTLY DO NOT CARE ABOUT

        'unsubscribed' => [
            'owner' => 'person',
            'type' => 'bool',
        ],*/
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
                    if ($appData) {
                        $obj->$k = $appData->$k;
                    }
            }
        }
        $obj->id = $application->id;

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
            if ($target !== null && empty($conf['domainOnly'])) {
                $this->copyTarget($target, $k, $v, $conf);
            }
        }
    }

    public function getFlattenedReference(array $supplemental = [])
    {
        $firstName = $this->firstName ?: 'unknown';
        $lastName = $this->lastName ?: 'unknown';

        return "{$firstName} {$lastName}";
    }

    public function toArray()
    {
        $output = parent::toArray();

        $output['meta'] = $this->meta;

        return $output;
    }

    public function __set($key, $value)
    {
        parent::__set($key, $value);

        // Automatically populate canDelete meta data
        if ($key === 'id') {
            $this->meta['canDelete'] = $this->isNew();
        }
    }

    /**
     * Is this a new Application?
     *
     * @return boolean True if object hasn't been persisted
     */
    public function isNew()
    {
        // Unset or negative ID means this is new
        return $this->id === null || $this->id < 0;
    }
}

<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Models a team application
 */
class ProgramLeader extends ParserDomain
{
    public $meta = [];

    protected static $validProperties = [
        'id' => [
            'owner' => 'person',
            'type' => 'int',
        ],
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
        'phone' => [
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
        'attendingWeekend' => [
            'owner' => 'centerStatsData',
            'type' => 'bool',
        ],
        'accountability' => [
            'owner' => 'meta',
            'type' => 'string',
        ],
    ];

    public static function fromModel($person, $centerStatsData = null, $options = [])
    {
        $ignore = array_get($options, 'ignore', false);
        $accountability = array_get($options, 'accountability', null);

        $obj = new static();

        if ($accountability) {
            $obj->accountability = $accountability;
        }

        foreach (static::$validProperties as $k => $v) {
            if ($ignore && array_get($ignore, $k, false)) {
                continue;
            }
            switch ($v['owner']) {
                case 'person':
                    if ($person) {
                        $obj->$k = $person->$k;
                    }
                    break;
                case 'centerStatsData':
                    if ($centerStatsData && $accountability) {
                        $csdKey = "{$accountability}AttendingWeekend";
                        $obj->$k = (bool) $centerStatsData->$csdKey;
                    }
                    break;
            }
        }

        return $obj;
    }

    public function fillModel($person, $centerStatsData = null, $only_set = true)
    {
        foreach ($this->_values as $k => $v) {
            if ($only_set && !array_key_exists($k, $this->_setValues)) {
                continue;
            }
            $conf = self::$validProperties[$k];
            switch ($conf['owner']) {
                case 'person':
                    $target = $teamMember->person;
                    break;
                case 'centerStatsData':
                    $target = $centerStatsData;
                    break;
            }
            if ($target !== null) {
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

}

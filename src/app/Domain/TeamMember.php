<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Models a team application
 */
class TeamMember extends ParserDomain
{
    public $meta = [];

    protected static $validProperties = [
        'id' => [
            'owner' => 'teamMember',
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
        'wbo' => [
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
            'owner' => 'teamMemberData',
            'type' => 'WithdrawCode',
            'assignId' => true,
        ],
        'comment' => [
            'owner' => 'teamMemberData',
            'type' => 'string',
        ],
        'accountabilities' => [
            'owner' => '__Accountability', // Marking a specialty object owner
            'type' => 'array',
        ],
        'quarterNumber' => [
            'owner' => 'teamMember',
            'type' => 'int',
        ],
    ];

    public static function fromModel($teamMemberData, $teamMember = null, $person = null, $options = [])
    {
        $ignore = array_get($options, 'ignore', false);
        if ($teamMember === null) {
            $teamMember = $teamMemberData->teamMember;
        }
        if ($person === null) {
            $person = $teamMember->person;
        }

        $obj = new static();
        foreach (static::$validProperties as $k => $v) {
            if ($ignore && array_get($ignore, $k, false)) {
                continue;
            }
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
                    break;
                case '__Accountability':
                    if (($reportingDate = array_get($options, 'accountabilitiesFor', null)) !== null) {
                        $obj->$k = $person->getAccountabilityIds($reportingDate);
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

    public static function fromArray($input, $requiredParams = [])
    {
        $member = parent::fromArray($input, $requiredParams);

        if ($member->incomingQuarter && $member->center) {
            // Ignore what we stashed, this is an ephemeral convenience value
            $member->quarterNumber = Models\TeamMember::getQuarterNumber($member->incomingQuarter, $member->center->region);
        }

        return $member;
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

    /**
     * Fetch the person associated with this TeamMember object.
     *
     * Will throw an exception if the person could not be found.
     *
     * @return Models\Person The person associated with this TeamMember domain.
     */
    public function getAssociatedPerson()
    {
        if (($personId = array_get($this->meta, 'personId', null)) !== null) {
            $person = Models\Person::find($personId);
            if ($person === null) {
                throw new \Exception("Unexpected: Could not find person with ID {$personId}");
            }
        } else {
            $teamMember = Models\TeamMember::find($this->id);
            if ($teamMember === null) {
                throw new \Exception("Unexpected: could not find team member with ID {$tm->id}");
            }
            $person = $teamMember->person;
        }

        return $person;
    }

}

<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Models a team application
 */
class NextQtrAccountability extends ParserDomain
{
    protected static $validProperties = [
        'id' => [
            'owner' => 'qtrAccountability',
            'type' => 'int',
        ],
        // if only name is set, then the accountability is someone not listed on the class list.
        'name' => [
            'owner' => 'qtrAccountability',
            'type' => 'string',
        ],
        'teamMember' => [
            'owner' => 'qtrAccountability',
            'type' => 'TeamMember',
            'assignId' => true,
        ],
        // If application is set, then it's an incoming team member who is designated.
        'application' => [
            'owner' => 'qtrAccountability',
            'type' => 'Application',
            'assignId' => true,
        ],
        'email' => [
            'owner' => 'qtrAccountability',
            'type' => 'string',
            'options' => ['trim' => true],
        ],
        'phone' => [
            'owner' => 'qtrAccountability',
            'type' => 'string',
            'options' => ['trim' => true],
        ],
        'notes' => [
            'owner' => 'qtrAccountability',
            'type' => 'string',
        ],
    ];

    public $meta;

    public static function fromArray($input, $requiredParams = [])
    {
        $obj = parent::fromArray($input, $requiredParams);
        $obj->meta = isset($input['submissionMeta']) ? $input['submissionMeta'] : [];

        return $obj;
    }

    public function toArray()
    {
        $v = parent::toArray();
        $v['meta'] = collect($this->meta)->map(function ($x) {return $x->toRfc3339String();});

        return $v;
    }

    public function getAccountability()
    {
        return Models\Accountability::find($this->id);
    }
}

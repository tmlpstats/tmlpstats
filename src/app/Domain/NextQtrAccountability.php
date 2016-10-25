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
        ],
        'phone' => [
            'owner' => 'qtrAccountability',
            'type' => 'string',
        ],
        'notes' => [
            'owner' => 'qtrAccountability',
            'type' => 'string',
        ],
    ];

    // Default fromArray and toArray are sufficient for us
}

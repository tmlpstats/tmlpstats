<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\TeamMember;

class TeamMemberParser extends IdParserBase
{
    protected $type = 'team member';
    protected $class = TeamMember::class;
    protected $allowObj = true;
}

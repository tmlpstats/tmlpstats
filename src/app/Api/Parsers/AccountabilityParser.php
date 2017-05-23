<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Accountability;

class AccountabilityParser extends IdParserBase
{
    protected $type = 'accountability';
    protected $class = Accountability::class;
}

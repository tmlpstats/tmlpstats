<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Center;

class CenterParser extends IdOrAbbrParserBase
{
    protected $type = 'center';
    protected $class = Center::class;
    protected $allowObj = true;
}

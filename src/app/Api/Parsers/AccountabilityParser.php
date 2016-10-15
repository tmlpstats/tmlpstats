<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Accountability;

class CourseParser extends IdParserBase
{
    protected $type = 'accountability';
    protected $class = Accountability::class;
}

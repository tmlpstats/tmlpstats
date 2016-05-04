<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Course;

class CourseParser extends IdParserBase
{
    protected $type = 'course';
    protected $class = Course::class;
}

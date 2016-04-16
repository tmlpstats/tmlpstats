<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\GlobalReport;

class GlobalReportParser extends IdParserBase
{
    protected $type = 'global report';
    protected $class = GlobalReport::class;
}

<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\StatsReport;

class LocalReportParser extends IdParserBase
{
    protected $type = 'local report';
    protected $class = StatsReport::class;
}

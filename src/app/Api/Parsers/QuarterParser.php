<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Quarter;

class QuarterParser extends IdParserBase
{
    protected $type = 'quarter';
    protected $class = Quarter::class;
}

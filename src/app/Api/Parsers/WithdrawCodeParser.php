<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\WithdrawCode;

class WithdrawCodeParser extends IdParserBase
{
    protected $type = 'WithdrawCode';
    protected $class = WithdrawCode::class;
}

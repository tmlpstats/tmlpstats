<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\WithdrawCode;

class WithdrawCodeParser extends IdParserBase
{
    protected $type = 'witdraw code';
    protected $class = WithdrawCode::class;
}

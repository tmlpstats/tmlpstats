<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\TmlpRegistration;

class ApplicationParser extends IdParserBase
{
    protected $type = 'application';
    protected $class = TmlpRegistration::class;
}

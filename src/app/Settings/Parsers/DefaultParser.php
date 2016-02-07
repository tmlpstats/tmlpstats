<?php
namespace TmlpStats\Settings\Parsers;

class DefaultParser extends AbstractParser
{
    /**
     * Parse the setting object and merge with defaults
     *
     * @return array
     * @throws \Exception
     */
    protected function parse()
    {
        return $this->decode();
    }
}

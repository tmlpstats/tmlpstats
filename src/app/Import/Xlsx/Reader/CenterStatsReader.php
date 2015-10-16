<?php
namespace TmlpStats\Import\Xlsx\Reader;

class CenterStatsReader extends ReaderAbstract
{
    protected $dataMap = array(
        'weekDate'  => array('format' => 'date'), // row and col passed as args
        'gameValue' => array('callback' => 'formatGameValue'), // row and col passed as args
    );

    protected function formatGameValue($name, $value)
    {
        // janky way of check if this value is a decimal. Unfortunately, all numbers come in as floats.
        if (!is_null($value) && preg_match("/\d+\.\d+/", "$value"))
        {
            $value = number_format($value * 100) . '%';
        }

        return $value;
    }
}

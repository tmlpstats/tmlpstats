<?php
namespace TmlpStats\Import\Xlsx\Reader;

class TmlpGameInfoReader extends ReaderAbstract
{
    protected $dataMap = array(
        'type'                   => array('col' => 'B'),
        'quarterStartRegistered' => array('col' => 'C'),
        'quarterStartApproved'   => array('col' => 'D'),
    );
}

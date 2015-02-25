<?php
namespace TmlpStats\Import\Xlsx\Reader\V11;

class CommCourseInfoReader extends ReaderAbstract
{
    protected $dataMap = array(
        'startDate'                  => array('col' => 'B', 'format' => 'date'),
        'quarterStartTer'            => array('col' => 'C'),
        'quarterStartStandardStarts' => array('col' => 'D'),
        'quarterStartXfer'           => array('col' => 'E'),
        'currentTer'                 => array('col' => 'F'),
        'currentStandardStarts'      => array('col' => 'G'),
        'currentXfer'                => array('col' => 'J'),
        'completedStandardStarts'    => array('col' => 'L'),
        'potentials'                 => array('col' => 'M'),
        'registrations'              => array('col' => 'N'),
    );
}

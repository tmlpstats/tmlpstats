<?php
namespace TmlpStats\Import\Xlsx\Reader\V11;

class ContactInfoReader extends ReaderAbstract
{
    protected $dataMap = array(
        'name'                            => array('col' => 'A'),
        'accountability'                  => array('col' => 'B'),
        'phone'                           => array('col' => 'C', 'format' => 'phone'),
        'email'                           => array('col' => 'D'),
        'reportingStatisticianName'       => array('col' => 'F', 'row' => 12),
        'reportingStatisticianPhone'      => array('col' => 'F', 'row' => 14, 'format' => 'phone'),
        'programManagerAttendingWeekend'  => array('col' => 'F', 'row' => 16),
        'classroomLeaderAttendingWeekend' => array('col' => 'F', 'row' => 17),
    );
}

<?php
namespace TmlpStats\Import\Xlsx\Reader;

class CommCourseInfoReader extends ReaderAbstract
{
    protected $dataMap = [
        'location'                   => ['col' => 'A'],
        'startDate'                  => ['col' => 'B', 'format' => 'date'],
        'quarterStartTer'            => ['col' => 'C'],
        'quarterStartStandardStarts' => ['col' => 'D'],
        'quarterStartXfer'           => ['col' => 'E'],
        'currentTer'                 => ['col' => 'F'],
        'currentStandardStarts'      => ['col' => 'G'],
        'currentXfer'                => ['col' => 'J'],
        'completedStandardStarts'    => ['col' => 'L'],
        'potentials'                 => ['col' => 'M'],
        'registrations'              => ['col' => 'N'],
        'guestsPromised'             => ['col' => 'P'],
        'guestsInvited'              => ['col' => 'Q'],
        'guestsConfirmed'            => ['col' => 'R'],
        'guestsAttended'             => ['col' => 'S'],
    ];
}

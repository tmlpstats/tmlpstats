<?php
namespace TmlpStats\Import\Xlsx\Reader;

use Carbon\Carbon;

class ClassListReader extends ReaderAbstract
{
    protected $dataMap = array(
        'firstName'         => array('col' => 'A'),
        'lastInitial'       => array('col' => 'B'),
        'completionQuarter' => array('col' => 'B', 'format' => 'date', 'callback' => 'formatCompletionQuarter'),
        'accountability'    => array('col' => 'D'),
        'wknd'              => array('col' => 'F'),
        'xferIn'            => array('col' => 'H'),
        'xferOut'           => array('col' => 'G'),
        'ctw'               => array('col' => 'I'),
        'wd'                => array('col' => 'J'),
        'wbo'               => array('col' => 'K'),
        'rereg'             => array('col' => 'L'),
        'excep'             => array('col' => 'M'),
        'travel'            => array('col' => 'Q'),
        'room'              => array('col' => 'R'),
        'comment'           => array('col' => 'O'),
        'gitw'              => array('col' => 'C'),
        'tdo'               => array('col' => 'E'),
    );

    // This is a total hack. We can get rid of it once we transition to a
    // web-based stats submission system.
    protected function formatCompletionQuarter($name, Carbon $date)
    {
        $month = $date->format('M');
        $year = $date->format('Y');

        $quarterString = '';

        // Set to values that we know are within the quarter
        switch($month)
        {
            case 'Nov':
                $quarterString = "Q4-{$year}";
                break;
            case 'Feb':
                $quarterString = "Q1-{$year}";
                break;
            case 'May':
                $quarterString = "Q2-{$year}";
                break;
            case 'Aug':
                $quarterString = "Q3-{$year}";
                break;
            default:
        }
        return $quarterString;
    }
}

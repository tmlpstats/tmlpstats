<?php
namespace TmlpStats\Import\Xlsx\Reader\V11;

class ClassListReader extends ReaderAbstract
{
    protected $dataMap = array(
        'firstName'         => array('col' => 'A'),
        'lastInitial'       => array('col' => 'B'),
        'completionQuarter' => array('col' => 'B', 'format' => 'date'),
        'accountability'    => array('col' => 'D'),
        'wknd'              => array('col' => 'F'),
        'xferIn'            => array('col' => 'H'),
        'xferOut'           => array('col' => 'G'),
        'ctw'               => array('col' => 'I'),
        'wd'                => array('col' => 'J'),
        'wbo'               => array('col' => 'K'),
        'rereg'             => array('col' => 'L'),
        'excep'             => array('col' => 'M'),
        'reasonWithdraw'    => array('col' => 'S'),
        'travel'            => array('col' => 'Q'),
        'room'              => array('col' => 'R'),
        'comment'           => array('col' => 'O'),
        'gitw'              => array('col' => 'C'),
        'tdo'               => array('col' => 'E'),
        'additionalTdo'     => array('col' => 'T'),
    );

    // This is a total hack. We can get rid of it once we transition to a
    // web-based stats submission system.
    protected function formatCompletionQuarter($dateStr)
    {
        // Passed in as YYYY-MM-DD timestamp, but day is inaccurate
        $timestamp = strtotime($dateStr);
        $month = date('M', $timestamp);
        $year = date('Y', $timestamp);

        $newDateStr = '';

        // Set to values that we know are within the quarter
        switch($month)
        {
            case 'Nov':
                $newDateStr = 'December 1';
                break;
            case 'Feb':
                $newDateStr = 'March 1';
                break;
            case 'May':
                $newDateStr = 'July 1';
                break;
            case 'Aug':
                $newDateStr = 'September 1';
                break;
            default:
        }
        return Carbon::createFromFormat('F j, Y', "$newDateStr, $year");
    }
}

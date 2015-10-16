<?php
namespace TmlpStats\Import\Xlsx\Reader;

class TmlpRegistrationReader extends ReaderAbstract
{
    protected $dataMap = array(
        'firstName'               => array('col' => 'A'),
        'lastInitial'             => array('col' => 'B'),
        'regDate'                 => array('col' => 'F', 'format' => 'date'),
        'weekendReg'              => array('col' => array('C', 'D', 'E')),
        'bef'                     => array('col' => 'C'),
        'dur'                     => array('col' => 'D'),
        'aft'                     => array('col' => 'E'),
        'incomingTeamYear'        => array('col' => array('C', 'D', 'E')),
        'appOut'                  => array('col' => 'G'),
        'appOutDate'              => array('col' => 'H', 'format' => 'date'),
        'appIn'                   => array('col' => 'I'),
        'appInDate'               => array('col' => 'J', 'format' => 'date'),
        'appr'                    => array('col' => 'K'),
        'apprDate'                => array('col' => 'L', 'format' => 'date'),
        'wd'                      => array('col' => 'M'),
        'wdDate'                  => array('col' => 'N', 'format' => 'date'),
        'committedTeamMemberName' => array('col' => 'Q'),
        'comment'                 => array('col' => 'V'),
        'travel'                  => array('col' => 'AE'),
        'room'                    => array('col' => 'AF'),
    );
    protected $reportingDate;

    public function setReportingDate($reportingDate)
    {
        $this->reportingDate = $reportingDate;
    }

    public function getWeekendReg($row)
    {
        $cols = $this->dataMap['weekendReg']['col'];
        $value = NULL;
        if (!$this->isEmptyCell($row, $cols[0]))
        {
            $value = 'before';
        }
        else if (!$this->isEmptyCell($row, $cols[1]))
        {
            $value = 'during';
        }
        else if (!$this->isEmptyCell($row, $cols[2]))
        {
            $value = 'after';
        }
        return $value;
    }

    public function getIncomingTeamYear($row)
    {
        $cols = $this->dataMap['incomingTeamYear']['col'];
        $col = NULL;
        foreach ($cols as $c)
        {
            $col = $c;
            if (!$this->isEmptyCell($row, $col))
            {
                break;
            }
        }
        return $col ? $this->getValue($row, $col) : NULL;
    }
}

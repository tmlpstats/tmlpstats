<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Course;
use TmlpStats\CourseData;
use TmlpStats\Util;

use Carbon\Carbon;

class CommCourseInfoImporter extends DataImporterAbstract
{
    protected $classDisplayName = "CAP & CPC Course Info.";

    protected static $blockCAP = array();
    protected static $blockCPC = array();

    protected function populateSheetRanges()
    {
        self::$blockCAP[] = $this->excelRange('A','O');
        self::$blockCAP[] = $this->excelRange(5,14);

        self::$blockCPC[] = $this->excelRange('A','O');
        self::$blockCPC[] = $this->excelRange(18,25);
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock(self::$blockCAP, 'CAP');
        $this->loadBlock(self::$blockCPC, 'CPC');
    }

    protected function loadEntry($row, $type)
    {
        if ($this->reader->isEmptyCell($row,'B')) return;

        $startDate = $this->reader->getStartDate($row);

        if ($startDate === false) {
            $startDateCol = $this->reader->getStartDateCol();
            $startDateRawValue = $this->reader->getValue($row, $startDateCol);
            $startDate = Util::parseUnknownDateFormat($startDateRawValue);
            $this->addMessage('COMMCOURSE_START_DATE_FORMAT_INVALID', $row, $type);

            if ($startDate === false) {
                $this->addMessage('COMMCOURSE_START_DATE_FORMAT_UNREADABLE', $row, $type);
            }
        }

        $course = Course::firstOrCreate(array(
            'center_id'  => $this->statsReport->center->id,
            'start_date' => $startDate ? $startDate->toDateString() : $startDateRawValue,
            'type'       => $type,
        ));
        if ($course->statsReportId === null) {
            $course->statsReportId = $this->statsReport->id;
            $course->save();
        }

        $courseData = CourseData::firstOrCreate(array(
            'center_id'      => $this->statsReport->center->id,
            'quarter_id'     => $this->statsReport->quarter->id,
            'course_id'      => $course->id,
            'reporting_date' => $this->statsReport->reportingDate->toDateString(),
        ));
        $courseData->offset = $row;
        $courseData->quarterStartTer            = $this->reader->getQuarterStartTer($row);
        $courseData->quarterStartStandardStarts = $this->reader->getQuarterStartStandardStarts($row);
        $courseData->quarterStartXfer           = $this->reader->getQuarterStartXfer($row);
        $courseData->currentTer                 = $this->reader->getCurrentTer($row);
        $courseData->currentStandardStarts      = $this->reader->getCurrentStandardStarts($row);
        $courseData->currentXfer                = $this->reader->getCurrentXfer($row);
        $courseData->completedStandardStarts    = $this->reader->getCompletedStandardStarts($row);
        $courseData->potentials                 = $this->reader->getPotentials($row);
        $courseData->registrations              = $this->reader->getRegistrations($row);
        $courseData->statsReportId              = $this->statsReport->id;
        $courseData->save();
    }
}

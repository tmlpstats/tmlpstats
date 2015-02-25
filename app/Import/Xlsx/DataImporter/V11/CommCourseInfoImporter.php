<?php
namespace TmlpStats\Import\Xlsx\DataImporter\V11;

use TmlpStats\Course;
use TmlpStats\CourseData;

use Carbon\Carbon;

use DB;

class CommCourseInfoImporter extends DataImporterAbstract
{
    protected $classDisplayName = "CAP & CPC Course Info";

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

        if (defined('IMPORT_HACKS')) {
            if ($startDate->lt($this->statsReport->quarter->startWeekendDate)) {
                // When you type just the month and day in excel, it defaults to the current year, but the spreadsheet
                // cell format doesn't show the year so it's easy to miss it. Adjust the year here.
                $startDate->addYear();
            }
        }
        if (!is_object($startDate)) {
            $startDate = Carbon::createFromFormat('m/d/y', $startDate)->startOfDay();
            $this->addMessage("Start date format is invalid for $type course.", 'error', $row);
        }

        $course = Course::firstOrCreate(array(
            'center_id'  => $this->statsReport->center->id,
            'start_date' => $startDate->toDateString(),
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

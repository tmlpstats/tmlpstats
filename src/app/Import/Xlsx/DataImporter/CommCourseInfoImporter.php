<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Course;
use TmlpStats\CourseData;
use TmlpStats\Util;

use Carbon\Carbon;

class CommCourseInfoImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_COURSES;

    protected function populateSheetRanges()
    {
        $cap                         = $this->findRange(3, 'Course Start Date', 'Total (Open Courses):', 'B', 'A');
        $this->blocks['cap']['cols'] = $this->excelRange('A', 'O');
        $this->blocks['cap']['rows'] = $this->excelRange($cap['start'] + 1, $cap['end']);

        $cpc                         = $this->findRange($cap['end'], 'Course Start Date', 'Total (Open Courses):', 'B', 'A');
        $this->blocks['cpc']['cols'] = $this->excelRange('A', 'O');
        $this->blocks['cpc']['rows'] = $this->excelRange($cpc['start'] + 1, $cpc['end']);
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock($this->blocks['cap'], 'CAP');
        $this->loadBlock($this->blocks['cpc'], 'CPC');
    }

    protected function loadEntry($row, $type)
    {
        if ($this->reader->isEmptyCell($row, 'B')) {
            return;
        }

        $this->data[] = [
            'type'                       => $type,
            'offset'                     => $row,
            'location'                   => $this->reader->getLocation($row),
            'startDate'                  => $this->reader->getStartDate($row),
            'quarterStartTer'            => $this->reader->getQuarterStartTer($row),
            'quarterStartStandardStarts' => $this->reader->getQuarterStartStandardStarts($row),
            'quarterStartXfer'           => $this->reader->getQuarterStartXfer($row),
            'currentTer'                 => $this->reader->getCurrentTer($row),
            'currentStandardStarts'      => $this->reader->getCurrentStandardStarts($row),
            'currentXfer'                => $this->reader->getCurrentXfer($row),
            'completedStandardStarts'    => $this->reader->getCompletedStandardStarts($row),
            'potentials'                 => $this->reader->getPotentials($row),
            'registrations'              => $this->reader->getRegistrations($row),
            'guestsPromised'             => $this->reader->getGuestsPromised($row),
            'guestsInvited'              => $this->reader->getGuestsInvited($row),
            'guestsConfirmed'            => $this->reader->getGuestsConfirmed($row),
            'guestsAttended'             => $this->reader->getGuestsAttended($row),
        ];
    }

    public function postProcess()
    {
        foreach ($this->data as $courseInput) {
            if (isset($this->data['errors'])) {
                continue;
            }

            $data = [
                'center_id'        => $this->statsReport->center->id,
                'start_date'       => $courseInput['startDate'],
                'type'             => $courseInput['type'],
            ];

            // London has a special situation where INTL and local stats are reported separately for courses
            if ($this->statsReport->center->name == 'London') {
                $data['is_international'] = (strtoupper($courseInput['location']) == 'INTL');
            }

            $course = Course::firstOrCreate($data);

            if ($course->location != $courseInput['location']) {
                $course->location = $courseInput['location'];
                $course->save();
            }

            $courseData = CourseData::firstOrNew([
                'course_id'       => $course->id,
                'stats_report_id' => $this->statsReport->id,
            ]);

            unset($courseInput['startDate']);
            unset($courseInput['type']);
            unset($courseInput['offset']);
            unset($courseInput['location']);

            $courseData = $this->setValues($courseData, $courseInput);
            $courseData->save();
        }
    }
}

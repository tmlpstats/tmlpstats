<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Reports\Arrangements;

class GlobalReportCoursesData
{
    private $globalReport;
    private $region;
    private $data = null;

    public function __construct(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $this->globalReport = $globalReport;
        $this->region = $region;
    }

    protected function getCoursesThisWeek($coursesData, Models\GlobalReport $globalReport, Models\Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->lt($globalReport->reportingDate)
                && $courseData->course->startDate->gt($globalReport->reportingDate->copy()->subWeek())
            ) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesNextMonthData($coursesData, Models\GlobalReport $globalReport, Models\Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->gt($globalReport->reportingDate)
                && $courseData->course->startDate->lt($globalReport->reportingDate->copy()->addWeeks(5))
            ) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesUpcoming($coursesData, Models\GlobalReport $globalReport, Models\Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->gt($globalReport->reportingDate)) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesCompleted($coursesData, Models\GlobalReport $globalReport, Models\Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->lt($globalReport->reportingDate)) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesGuestGames($coursesData, Models\GlobalReport $globalReport, Models\Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->guestsPromised !== null) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    /** CLASSIC METHOD - getCoursesAll */
    public function getCoursesAllClassic(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $statusTypes = [
            'coursesthisweek',
            'coursesnextmonth',
            'coursesupcoming',
            'coursescompleted',
            'coursesguestgames',
        ];

        $responseData = [];
        foreach ($statusTypes as $type) {
            $response = $this->getOne($type);
            $responseData[$type] = $response ? $response->render() : '';
        }

        return $responseData;
    }

    public function getOne($page)
    {
        $globalReport = $this->globalReport;
        $region = $this->region;

        $data = $this->data;
        if (!$this->data) {
            $this->data = $data = App::make(Api\GlobalReport::class)->getCourseList($globalReport, $region);
            if (!$data) {
                return null;
            }
        }

        $targetData = null;
        $type = null;
        $byType = true;
        $flatten = true;
        switch ($page) {
            case 'coursesthisweek':
            case 'CoursesThisWeek':
                $targetData = $this->getCoursesThisWeek($data, $globalReport, $region);
                $type = 'completed';
                break;
            case 'coursesnextmonth':
            case 'CoursesNextMonth':
                $targetData = $this->getCoursesNextMonthData($data, $globalReport, $region);
                $type = 'next5weeks';
                $flatten = false;
                break;
            case 'coursesupcoming':
            case 'CoursesUpcoming':
                $targetData = $this->getCoursesUpcoming($data, $globalReport, $region);
                $type = 'upcoming';
                $flatten = false;
                break;
            case 'coursescompleted':
            case 'CoursesCompleted':
                $targetData = $this->getCoursesCompleted($data, $globalReport, $region);
                $type = 'completed';
                break;
            case 'coursesguestgames':
            case 'CoursesGuestGames':
                $targetData = $this->getCoursesGuestGames($data, $globalReport, $region);
                $type = 'guests';
                break;
            default:
                throw new \Exception("Unkown page $page");
        }

        return $this->displayCoursesReport($targetData, $globalReport, $type, $byType, $flatten);
    }

    protected function displayCoursesReport($coursesData, Models\GlobalReport $globalReport, $type, $byType = false, $flatten = false)
    {
        $a = new Arrangements\CoursesByCenter(['coursesData' => $coursesData]);
        $coursesByCenter = $a->compose();
        $coursesByCenter = $coursesByCenter['reportData'];

        $statsReports = [];
        $centerReportData = [];
        foreach ($coursesByCenter as $centerName => $coursesData) {
            $a = new Arrangements\CoursesWithEffectiveness([
                'courses' => $coursesData,
                'reportingDate' => $globalReport->reportingDate,
            ]);
            $centerRow = $a->compose();

            $centerReportData[$centerName] = $centerRow['reportData'];
            $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Models\Center::name($centerName)->first());
        }
        ksort($centerReportData);

        if ($byType) {
            $typeReportData = [
                'CAP' => [],
                'CPC' => [],
                'completed' => [],
            ];

            foreach ($centerReportData as $centerName => $coursesData) {
                foreach ($coursesData as $courseType => $courseTypeData) {
                    foreach ($courseTypeData as $courseData) {
                        $typeReportData[$courseType][] = $courseData;
                    }
                }
            }

            if ($flatten) {
                $reportData = [];
                foreach (['CAP', 'CPC', 'completed'] as $courseType) {
                    if (isset($typeReportData[$courseType])) {
                        foreach ($typeReportData[$courseType] as $data) {
                            $reportData[] = $data;
                        }
                    }
                }
            } else {
                // Make sure they come out in the right order
                foreach ($typeReportData as $courseType => $data) {
                    if (!$data) {
                        unset($typeReportData[$courseType]);
                    }
                }
                $reportData = $typeReportData;
            }
        } else {
            $reportData = $centerReportData;
        }

        return view('globalreports.details.courses', compact('reportData', 'type', 'statsReports'));
    }
}

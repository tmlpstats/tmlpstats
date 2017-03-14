<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Contracts\Referenceable;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;

/**
 * Validation data
 */
class ValidationData extends AuthenticatedApiBase
{
    protected $dataTypesConf = [
        'Application' => [
            'apiClass' => Application::class,
            'typeName' => 'TeamApplication',
        ],
        'TeamMember' => [
            'apiClass' => TeamMember::class,
            'typeName' => 'TeamMember',
        ],
        'Course' => [
            'apiClass' => Course::class,
            'typeName' => 'Course',
        ],
        'Scoreboard' => [
            'apiClass' => Scoreboard::class,
            'typeName' => 'Scoreboard',
        ],
    ];

    public function validate(Models\Center $center, Carbon $reportingDate)
    {
        $this->assertAuthz($this->context->can('submitStats', $center) || $this->context->can('viewSubmissionUi', $center));
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $report = LocalReport::ensureStatsReport($center, $reportingDate);

        $results = $this->validateSubmissionData($report);
        $isValid = true;

        foreach ($results as $group => $groupData) {
            foreach ($groupData as $message) {
                if ($message->level() == 'error') {
                    $isValid = false;
                    break;
                }
            }
        }

        return [
            'success' => true,
            'valid' => $isValid,
            'messages' => $results,
        ];
    }

    protected function validateSubmissionData(Models\StatsReport $report)
    {
        $cq = Encapsulations\CenterReportingDate::ensure($report->center, $report->reportingDate)->getCenterQuarter();
        $isFirstWeek = $report->reportingDate->eq($cq->firstWeekDate);

        $data = [];
        $pastWeeks = [];
        foreach ($this->dataTypesConf as $group => $conf) {
            $data[$conf['typeName']] = App::make($conf['apiClass'])->getWeekSoFar(
                $report->center,
                $report->reportingDate
            );

            // We don't care about last week's data if this is the first week of the quarter
            if ($isFirstWeek) {
                continue;
            }

            $pastWeeks[$conf['typeName']] = App::make($conf['apiClass'])->getWeekSoFar(
                $report->center,
                $report->reportingDate->copy()->subWeek(),
                false
            );
        }

        $results = [];
        $validationResults = $this->validateAll($report, $data, $pastWeeks);
        if ($validationResults['messages']) {
            $results = $validationResults['messages'];
        }

        return $results;
    }
}

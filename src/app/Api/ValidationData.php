<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Traits;
use TmlpStats\Contracts\Referenceable;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;

/**
 * Validation data
 */
class ValidationData extends AuthenticatedApiBase
{
    use Traits\ValidatesObjects, Traits\UsesReportDates;

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
        'ProgramLeader' => [
            'apiClass' => ProgramLeader::class,
            'typeName' => 'ProgramLeader',
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
        $data = [];
        $pastWeeks = [];
        foreach ($this->dataTypesConf as $group => $conf) {
            $data[$conf['typeName']] = App::make($conf['apiClass'])->getWeekSoFar(
                $report->center,
                $report->reportingDate
            );

            // We don't care about last week's data if this is the first week of the quarter, unless this is a Course
            $lastReportingDate = $this->lastReportingDate($report->center, $report->reportingDate, ($conf['typeName'] === 'Course'));
            if (!$lastReportingDate) {
                continue;
            }

            $pastWeeks[$conf['typeName']] = App::make($conf['apiClass'])->getWeekSoFar(
                $report->center,
                $lastReportingDate,
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

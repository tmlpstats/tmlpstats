<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Contracts\Referenceable;
use TmlpStats\Domain;

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
        $data = [];
        foreach ($this->dataTypesConf as $group => $conf) {
            $data[$conf['typeName']] = App::make($conf['apiClass'])->getWeekSoFar(
                $report->center,
                $report->reportingDate
            );
        }

        $results = [];
        $validationResults = $this->validateAll($report, $data);
        if ($validationResults['messages']) {
            $results = $validationResults['messages'];
        }

        return $results;
    }
}

<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Domain;

/**
 * Validation data
 */
class ValidationData extends AuthenticatedApiBase
{
    public function validate(Models\Center $center, Carbon $reportingDate)
    {
        $this->assertAuthz($this->context->can('viewSubmissionUi', $center));
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $validationResults = $this->validateSubmissionData($center, $reportingDate);

        return [
            'success' => true,
            'results' => $validationResults,
        ];
    }

    protected function validateSubmissionData(Models\Center $center, Carbon $reportingDate)
    {
        $types = [
            'applications' => Domain\TeamApplication::class,
            'courses' => Domain\Course::class,
            'scoreboard' => Domain\Scoreboard::class,
        ];
        $report = LocalReport::getStatsReport($center, $reportingDate);

        $results = [];
        foreach ($types as $group => $type) {
            $submissionData = App::make(SubmissionData::class)->allForType($center, $reportingDate, $type);

            foreach ($submissionData as $object) {
                $id = $object->getId();
                $validationResults = $this->validateObject($report, $object, $id);

                $results[$group][] = [
                    'valid' => $validationResults['valid'],
                    'messages' => $validationResults['messages'],
                ];
            }
        }

        return $results;
    }
}

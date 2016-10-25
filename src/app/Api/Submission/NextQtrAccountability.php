<?php
namespace TmlpStats\Api\Submission;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Api\Exceptions;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;

// QtrAccountability handles after-classroom-3 accountabilities
class NextQtrAccountability
{
    public function allForCenter(Models\Center $center, Carbon $reportingDate)
    {
        $cr = Encapsulations\CenterReportingDate::ensure($center, $reportingDate);
        $centerQuarter = $cr->getCenterQuarter();
        $allAccountabilities = [];
        $found = App::make(Api\SubmissionData::class)
            ->allForTypeWholeQuarter($centerQuarter, Domain\NextQtrAccountability::class);

        foreach ($found as $qtrAccountability) {
            $allAccountabilities[$qtrAccountability->id] = $qtrAccountability;
        }

        return array_values($allAccountabilities);
    }

    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        $submissionData = App::make(Api\SubmissionData::class);

        $accountabilityId = array_get($data, 'id', null);
        // Check accountability is a real one by fetching
        if (Models\Accountability::find($accountabilityId) === null) {
            throw new Exceptions\BadRequestException("Accountability with ID {$accountabilityId} does not exist");
        }

        // email and phone must be able to be blanked out for saving incomplete accountabilities back.
        $qa = Domain\NextQtrAccountability::fromArray($data, ['id', 'name']);

        $submissionData->store($center, $reportingDate, $qa);

        // currently no validation exists
        //    $report = LocalReport::ensureStatsReport($center, $reportingDate);
        //   $validationResults = $this->validateObject($report, $qa, $someId);

        return ['storedId' => $accountabilityId];
    }
}

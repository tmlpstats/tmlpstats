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
            // Override stashed object with actual person's data
            if ($person = $this->getPerson($qtrAccountability)) {
                $qtrAccountability->phone = $person->phone;
                $qtrAccountability->email = $person->email;
            }

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
        $nqa = Domain\NextQtrAccountability::fromArray($data, ['id', 'name']);

        $submissionData->store($center, $reportingDate, $nqa);

        // Merge email and phone into an object. Used to update local stashes.
        $mergeit = function ($obj) use ($nqa) {
            if ($nqa->phone ?? false) {
                $obj->phone = $nqa->phone;
            }
            if ($nqa->email ?? false) {
                $obj->email = $nqa->email;
            }
        };

        // Update person object if relevant.
        if ($person = $this->getPerson($nqa)) {
            $mergeit($person);
            $person->save();
        }

        // Update TeamMember stash if one already exists.
        if ($nqa->teamMemberId) {
            if ($tmd = $submissionData->get($center, $reportingDate, Domain\TeamMember::class, $nqa->teamMemberId)) {
                $mergeit($tmd);
                $submissionData->store($center, $reportingDate, $tmd);
            }
        }

        // Update Application stash if one already exists.
        if ($nqa->applicationId) {
            if ($app = $submissionData->get($center, $reportingDate, Domain\TeamApplication::class, $nqa->applicationId)) {
                $mergeit($app);
                $submissionData->store($center, $reportingDate, $app);
            }
        }

        // currently no validation exists
        //    $report = LocalReport::ensureStatsReport($center, $reportingDate);
        //   $validationResults = $this->validateObject($report, $nqa, $someId);

        return ['storedId' => $accountabilityId];
    }

    protected function getPerson(Domain\NextQtrAccountability $nqa)
    {
        if ($nqa->teamMember) {
            return $nqa->teamMember->person;
        }
        if ($nqa->application) {
            return $nqa->application->person;
        }

        return null;
    }
}

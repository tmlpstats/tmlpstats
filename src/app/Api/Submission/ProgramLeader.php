<?php
namespace TmlpStats\Api\Submission;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Traits;
use TmlpStats\Domain;

/**
 * ProgramLeaders
 */
class ProgramLeader extends AuthenticatedApiBase
{
    use Traits\UsesReportDates, Traits\ValidatesObjects;

    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false)
    {
        App::make(Api\SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $programManager = $center->getProgramManager($reportingDate);
        $classroomLeader = $center->getClassroomLeader($reportingDate);

        $csd = null;
        if ($programManager || $classroomLeader) {
            $lastReport = $this->relevantReport($center, $reportingDate);
            if ($lastReport) {
                $csd = $lastReport->centerStatsData()
                                  ->actual()
                                  ->reportingDate($lastReport->reportingDate)
                                  ->first();
            }
        }

        $pm = null;
        if ($programManager) {
            $pm = Domain\ProgramLeader::fromModel($programManager, $csd, ['accountability' => 'programManager']);
        }

        $cl = null;
        if ($classroomLeader) {
            $cl = Domain\ProgramLeader::fromModel($classroomLeader, $csd, ['accountability' => 'classroomLeader']);
        }

        // If PM and CL are the same person, align value for attendingWeekend
        if ($pm && $cl && $pm->id == $cl->id && $pm->attendingWeekend !== $cl->attendingWeekend) {
            $pm->attendingWeekend = $cl->attendingWeekend = true;
        }

        $programLeaders = [
            'meta' => [
                'programManager' => null,
                'classroomLeader' => null,
            ],
        ];

        if ($pm) {
            $programLeaders[$pm->id] = $pm;
            $programLeaders['meta']['programManager'] = $pm->id;
        }

        if ($cl) {
            $programLeaders[$cl->id] = $cl;
            $programLeaders['meta']['classroomLeader'] = $cl->id;
        }

        if ($includeInProgress) {
            $submissionData = App::make(Api\SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\ProgramLeader::class);
            foreach ($found as $domain) {
                $domain->meta['localChanges'] = true;
                $programLeaders[$domain->id] = $domain;
                $programLeaders['meta'][$domain->accountability] = $domain->id;
            }
        }

        return $programLeaders;
    }

    /**
     * Stash an in-progress weekly program leader data.
     * @param  Models\Center $center        The center of this report submission
     * @param  Carbon        $reportingDate The reportingDate of this submission
     * @param  array         $data          Information for ProgramLeader domain
     * @return [type]
     */
    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        App::make(Api\SubmissionCore::class)->checkCenterDate($center, $reportingDate, ['write']);

        $this->assertCan('submitStats', $center);

        $submissionData = App::make(Api\SubmissionData::class);
        $programLeaderId = $submissionData->numericStorageId($data, 'id');

        if ($programLeaderId !== null && $programLeaderId > 0) {
            $person = Models\Person::findOrFail($programLeaderId);
            $domain = Domain\ProgramLeader::fromModel($person);
            $domain->updateFromArray($data, ['accountability']);
        } else {
            $domain = Domain\ProgramLeader::fromArray($data);
        }

        $report = Api\LocalReport::ensureStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $domain, $programLeaderId);

        if (!isset($data['_idGenerated']) || $validationResults['valid']) {
            $submissionData->store($center, $reportingDate, $domain);
        } else {
            return [
                'success' => false,
                'valid' => $validationResults['valid'],
                'messages' => $validationResults['messages'],
            ];
        }

        return [
            'success' => true,
            'storedId' => $programLeaderId,
            'valid' => $validationResults['valid'],
            'messages' => $validationResults['messages'],
        ];
    }

    public function getWeekSoFar(Models\Center $center, Carbon $reportingDate, $includeInProgress = true)
    {
        $data = $this->allForCenter($center, $reportingDate, $includeInProgress);
        unset($data['meta']);

        return $data;
    }
}

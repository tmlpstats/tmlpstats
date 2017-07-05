<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Traits;
use TmlpStats\Domain;

/**
 * TeamMembers
 */
class TeamMember extends AuthenticatedApiBase
{
    use Traits\UsesReportDates, Traits\ValidatesObjects;

    private static $omitGitwTdo = ['tdo' => true, 'gitw' => true];

    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $allTeamMembers = [];

        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\TeamMember::class);
            foreach ($found as $domain) {
                $allTeamMembers[$domain->id] = $domain;
                $domain->meta['localChanges'] = true;
            }
        }

        $lastReport = $this->relevantReport($center, $reportingDate);
        if ($lastReport) {
            // If the last report happens to be the same week as this week, we can include GITW/TDO.
            $includeData = ($lastReport->reportingDate->eq($reportingDate));
            $options = [
                'ignore' => ($includeData) ? false : self::$omitGitwTdo,
                // setting time explicitly here to make sure we display accountabiles up to the time this report is active
                'accountabilitiesFor' => $reportingDate->copy()->setTime(15, 0, 0),
            ];
            foreach (App::make(LocalReport::class)->getClassList($lastReport) as $tmd) {
                // it's a small optimization, but prevent creating domain if we have an existing SubmissionData version
                if (!array_key_exists($tmd->teamMemberId, $allTeamMembers)) {
                    $domain = Domain\TeamMember::fromModel($tmd, $tmd->teamMember, null, $options);
                    $domain->meta['fromReport'] = true;
                    $allTeamMembers[$domain->id] = $domain;
                } else {
                    $domain = $allTeamMembers[$tmd->teamMemberId];

                }
                $domain->meta['personId'] = $tmd->teamMember->personId;
                $domain->meta['hasThisWeekReportData'] = ($includeData);
            }

        }

        return $allTeamMembers;
    }

    /**
     * Stash an in-progress weekly team-member data.
     * @param  Models\Center $center        The center of this report submission
     * @param  Carbon        $reportingDate The reportingDate of this submission
     * @param  array         $data          Information for TeamMember domain (includes keys from teamMember, person, and TeamMember)
     * @return [type]
     */
    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate, ['write']);

        $this->assertCan('submitStats', $center);

        $submissionData = App::make(SubmissionData::class);
        $teamMemberId = $submissionData->numericStorageId($data, 'id');

        $pastWeeks = [];

        if ($teamMemberId !== null && $teamMemberId > 0) {
            $tm = Models\TeamMember::findOrFail($teamMemberId);
            $domain = Domain\TeamMember::fromModel(null, $tm);
            $domain->updateFromArray($data, ['incomingQuarter']);

            $pastWeeks = $this->getPastWeeksData($center, $reportingDate, $tm);
        } else {
            $domain = Domain\TeamMember::fromArray($data, ['incomingQuarter', 'teamYear']);
        }
        $report = LocalReport::ensureStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $domain, $teamMemberId, $pastWeeks);

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
            'storedId' => $teamMemberId,
            'valid' => $validationResults['valid'],
            'messages' => $validationResults['messages'],
        ];
    }

    public function bulkStashWeeklyReporting(Models\Center $center, Carbon $reportingDate, array $updates)
    {
        $this->assertCan('submitStats', $center);
        $submissionData = App::make(SubmissionData::class);
        $sourceData = $this->allForCenter($center, $reportingDate, true);
        $report = LocalReport::ensureStatsReport($center, $reportingDate);
        $messages = [];
        foreach ($updates as $item) {
            $updatedDomain = Domain\TeamMember::fromArray($item, ['id']);
            $existing = array_get($sourceData, $updatedDomain->id, null);
            $existing->gitw = $updatedDomain->gitw;
            $existing->tdo = $updatedDomain->tdo;
            $submissionData->store($center, $reportingDate, $existing);
            $validationResults = $this->validateObject($report, $existing, $existing->id);
            $messages[$updatedDomain->id] = $validationResults['messages'];
        }

        return ['messages' => $messages];
    }

    public function setWeekData(Models\TeamMember $teamMember, Carbon $reportingDate, array $data)
    {
        $this->assertAuthz($this->context->can('submitStats', $teamMember->person->center));

        $report = LocalReport::ensureStatsReport($teamMember->center, $reportingDate);

        $teamMemberData = Models\TeamMemberData::firstOrCreate([
            'team_member_id' => $teamMember->id,
            'stats_report_id' => $report->id,
        ]);

        $domain = Domain\TeamMember::fromModel($teamMemberData, $teamMember, $teamMember->person);
        $domain->clearSetValues();
        $domain->updateFromArray($data);
        $domain->fillModel($teamMemberData, $teamMember);

        if (!$teamMemberData->statsReportId) {
            $teamMemberData->statsReportId = $report->id;
        }

        if ($teamMemberData->isDirty()) {
            $teamMemberData->save();
        }

        if ($teamMember->isDirty()) {
            $teamMember->save();
        }

        return $teamMemberData->load('teamMember.person', 'teamMember.incomingQuarter', 'statsReport', 'withdrawCode');
    }

    public function getWeekSoFar(Models\Center $center, Carbon $reportingDate, $includeInProgress = true)
    {
        return $this->allForCenter($center, $reportingDate, $includeInProgress);
    }

    protected function getPastWeeksData(Models\Center $center, Carbon $reportingDate, Models\TeamMember $member)
    {
        $lastWeekReportingDate = $this->lastReportingDate($center, $reportingDate);
        if (!$lastWeekReportingDate) {
            return [];
        }

        $lastReport = $this->relevantReport($center, $lastWeekReportingDate);
        if (!$lastReport) {
            return [];
        }

        $lastWeekData = Models\TeamMemberData::byStatsReport($lastReport)->byTeamMember($member)->first();
        if (!$lastWeekData) {
            return [];
        }

        return [
            Domain\TeamMember::fromModel($lastWeekData, $member),
        ];
    }

    /**
     * Return a list of valid CenterQuarters that someone can use as a starting quarter.
     * @param  Models\Center  $center        The center we care about
     * @param  Carbon         $reportingDate The current reporting date to use as reference.
     * @param  Models\Quarter $startQuarter  If provided, a reference start quarter to help prevent lookups.
     * @return array<Domain\CenterQuarter>
     */
    public function validStartQuarters(Models\Center $center, Carbon $reportingDate, Models\Quarter $currentQuarter = null)
    {
        if ($currentQuarter == null) {
            $currentQuarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);
        }

        // Get 1 year of prior quarters in a single shot, save extra queries.
        // If right now is Q3 2015, this executes the query similar to:
        //   WHERE (year = 2014 AND quarter >= 3) OR (year=2015 AND quarter < 3)
        $result = Models\Quarter::where(function ($query) use ($currentQuarter) {
            $query->where('year', $currentQuarter->year - 1)
                  ->where('quarter_number', '>', $currentQuarter->quarterNumber);
        })->orWhere(function ($query) use ($currentQuarter) {
            $query->where('year', $currentQuarter->year)
                  ->where('quarter_number', '<', $currentQuarter->quarterNumber);
        })->get();

        // Now we have 5 quarters when including the current one.
        $result->push($currentQuarter);

        // Return each one as a CenterQuarter. Make use of the collection features of laravel.

        return $result->map(function ($q) use ($center) {
            return Domain\CenterQuarter::ensure($center, $q);
        });
    }
}

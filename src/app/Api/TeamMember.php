<?php namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Domain;

/**
 * TeamMembers
 */
class TeamMember extends AuthenticatedApiBase
{
    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false)
    {

        $allTeamMembers = [];

        $quarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);

        // Get the last stats report in order to pre-populate the class list effectively
        $lastReport = Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->official()
            ->where('reporting_date', '<=', $reportingDate)
            ->orderBy('reporting_date', 'desc')
            ->first();

        if ($lastReport) {
            // If the last report happens to be the same week as this week, we can include applicationData.
            $includeData = ($lastReport->reportingDate->eq($reportingDate));
            foreach (App::make(LocalReport::class)->getClassList($lastReport) as $tmd) {
                if ($includeData) {
                    $domain = Domain\TeamMember::fromModel($tmd, $tmd->teamMember);
                } else {
                    $domain = Domain\TeamMember::fromModel(null, $tmd->teamMember);
                }
                $allTeamMembers[$domain->id] = $domain;
            }
        }

        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\TeamMember::class);
            foreach ($found as $domain) {
                // TODO decide if we want to merge with team member or just clobber like we're heading towards.
                $allTeamMembers[$domain->id] = $domain;
            }
        }

        return array_values($allTeamMembers);
    }

    public function create(array $data)
    {
        $domain = Domain\TeamMember::fromArray($data, ['firstName', 'lastName', 'center', 'teamYear', 'incomingQuarter']);
        $this->assertAuthz($this->context->can('submitStats', $domain->center));

        $memberQuarterNumber = Models\TeamMember::getQuarterNumber($domain->incomingQuarter, $domain->center->region);

        $teamMember = Models\TeamMember::firstOrNew([
            'first_name' => $domain->firstName,
            'last_name' => $domain->lastName,
            'center_id' => $domain->center->id,
            'team_year' => $domain->teamYear,
            'incoming_quarter_id' => $domain->incomingQuarter->id,
            'team_quarter' => $memberQuarterNumber,
        ]);

        $teamMember->person->email = $domain->email;
        $teamMember->person->phone = $domain->phone;
        $teamMember->isReviewer = $domain->isReviewer ?: false;

        if ($teamMember->person->isDirty()) {
            $teamMember->person->save();
        }
        $teamMember->save();

        return $teamMember->load('person');
    }

    public function update(Models\TeamMember $teamMember, array $data)
    {
        $domain = Domain\TeamMember::fromArray($data);
        $this->assertAuthz($this->context->can('submitStats', $teamMember->center));

        $domain->fillModel(null, $teamMember, true);

        if ($teamMember->person->isDirty()) {
            $teamMember->person->save();
        }
        if ($teamMember->isDirty()) {
            $teamMember->save();
        }

        return $teamMember->load('person');
    }

    public function setWeekData(Models\TeamMember $teamMember, Carbon $reportingDate, array $data)
    {
        $this->assertAuthz($this->context->can('submitStats', $teamMember->person->center));

        $report = LocalReport::getStatsReport($teamMember->center, $reportingDate);

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
}

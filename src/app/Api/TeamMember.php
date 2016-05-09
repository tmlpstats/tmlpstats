<?php namespace TmlpStats\Api;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;

/**
 * TeamMembers
 */
class TeamMember extends ApiBase
{
    protected $cacheEnabled = false;
    protected $validProperties = [
        'firstName' => [
            'owner' => 'person',
            'type'  => 'string',
        ],
        'lastName' => [
            'owner' => 'person',
            'type'  => 'string',
        ],
        'phone' => [
            'owner' => 'person',
            'type'  => 'string',
        ],
        'email' => [
            'owner' => 'person',
            'type'  => 'string',
        ],
        'center' => [
            'owner' => 'person',
            'type'  => 'Center',
        ],
        'teamYear' => [
            'owner' => 'teamMember',
            'type'  => 'int',
        ],
        'incomingQuarter' => [
            'owner' => 'teamMember',
            'type'  => 'Quarter',
        ],
        'isReviewer' => [
            'owner' => 'teamMember',
            'type'  => 'bool',
        ],
        'atWeekend' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'xferIn' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'xferOut' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'ctw' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'rereg' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'except' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'travel' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'room' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'gitw' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'tdo' => [
            'owner' => 'teamMemberData',
            'type'  => 'bool',
        ],
        'withdrawCode' => [
            'owner' => 'applicationData',
            'type'  => 'WithdrawCode',
        ],
        'comment' => [
            'owner' => 'applicationData',
            'type'  => 'string',
        ],
    ];

    public function create(array $data)
    {
        $data = $this->parseInputs($data, ['firstName', 'lastName', 'center', 'teamYear', 'incomingQuarter']);

        $memberQuarterNumber = Models\TeamMember::getQuarterNumber($data['incomingQuarter'], $data['center']->region);

        $teamMember = Models\TeamMember::firstOrNew([
            'first_name'          => $data['firstName'],
            'last_name'           => $data['lastName'],
            'center_id'           => $data['center']->id,
            'team_year'           => $data['teamYear'],
            'incoming_quarter_id' => $data['incomingQuarter']->id,
            'team_quarter'        => $memberQuarterNumber,
        ]);

        if (isset($data['email'])) {
            $teamMember->person->email = $data['email'];
        }
        if (isset($data['phone'])) {
            $teamMember->person->phone = $data['phone'];
        }
        if (isset($data['isReviewer'])) {
            $teamMember->isReviewer = $data['isReviewer'];
        }

        if ($teamMember->person->isDirty()) {
            $teamMember->person->save();
        }
        $teamMember->save();

        return $teamMember->load('person');
    }

    public function update(Models\TeamMember $teamMember, array $data)
    {
        $data = $this->parseInputs($data);

        foreach ($data as $property => $value) {
            if ($this->validProperties[$property]['owner'] == 'teamMember') {
                if ($teamMember->$property !== $value) {
                    $teamMember->$property = $value;
                }
            }
            if ($this->validProperties[$property]['owner'] == 'person') {
                if ($teamMember->person->$property !== $value) {
                    $teamMember->person->$property = $value;
                }
            }
        }

        if ($teamMember->person->isDirty()) {
            $teamMember->person->save();
        }
        if ($teamMember->isDirty()) {
            $teamMember->save();
        }

        return $teamMember->load('person');
    }

    public function getWeekData(Models\TeamMember $teamMember, Carbon $reportingDate)
    {
        $cached = $this->checkCache(compact('teamMember', 'reportingDate'));
        if ($cached) {
            return $cached;
        }

        $report = LocalReport::getStatsReport($teamMember->center, $reportingDate);

        $response = Models\TeamMemberData::firstOrCreate([
            'team_member_id'  => $teamMember->id,
            'stats_report_id' => $report->id,
        ])->load('teamMember.person', 'teamMember.incomingQuarter', 'statsReport', 'withdrawCode');

        $this->putCache($response);

        return $response;
    }

    public function setWeekData(Models\TeamMember $teamMember, Carbon $reportingDate, array $data)
    {
        $data = $this->parseInputs($data);

        $report = LocalReport::getStatsReport($teamMember->center, $reportingDate);

        $teamMemberData = Models\TeamMemberData::firstOrCreate([
            'team_member_id'  => $teamMember->id,
            'stats_report_id' => $report->id,
        ]);

        foreach ($data as $property => $value) {
            if ($this->validProperties[$property]['owner'] == 'teamMemberData') {
                if (($teamMemberData->$property instanceof Carbon) && Carbon::parse($value)
                                                                            ->ne($teamMemberData->$property)
                ) {
                    $teamMemberData->$property = Carbon::parse($value)->startOfDay();
                } else if ($teamMemberData->$property !== $value) {
                    $teamMemberData->$property = $value;
                }
            }
            if ($this->validProperties[$property]['owner'] == 'teamMember') {
                if ($teamMember->$property !== $value) {
                    $teamMember->$property = $value;
                }
            }
        }

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


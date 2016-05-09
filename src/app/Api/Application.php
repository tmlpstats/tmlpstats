<?php namespace TmlpStats\Api;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;

/**
 * Applications
 */
class Application extends ApiBase
{
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
        'unsubscribed' => [
            'owner' => 'person',
            'type'  => 'bool',
        ],
        'teamYear' => [
            'owner' => 'application',
            'type'  => 'int',
        ],
        'regDate' => [
            'owner' => 'application',
            'type'  => 'date',
        ],
        'isReviewer' => [
            'owner' => 'application',
            'type'  => 'bool',
        ],
        'appOutDate' => [
            'owner' => 'applicationData',
            'type'  => 'date',
        ],
        'appInDate' => [
            'owner' => 'applicationData',
            'type'  => 'date',
        ],
        'apprDate' => [
            'owner' => 'applicationData',
            'type'  => 'date',
        ],
        'wdDate' => [
            'owner' => 'applicationData',
            'type'  => 'date',
        ],
        'withdrawCode' => [
            'owner' => 'applicationData',
            'type'  => 'WithdrawCode',
        ],
        'committedTeamMember' => [
            'owner' => 'applicationData',
            'type'  => 'TeamMember',
        ],
        'incomingQuarter' => [
            'owner' => 'applicationData',
            'type'  => 'Quarter',
        ],
        'comment' => [
            'owner' => 'applicationData',
            'type'  => 'string',
        ],
        'travel' => [
            'owner' => 'applicationData',
            'type'  => 'bool',
        ],
        'room' => [
            'owner' => 'applicationData',
            'type'  => 'bool',
        ],
    ];

    public function create(array $data)
    {
        $data = $this->parseInputs($data, ['firstName', 'lastName', 'center', 'teamYear', 'regDate']);

        $application = Models\TmlpRegistration::firstOrNew([
            'first_name' => $data['firstName'],
            'last_name'  => $data['lastName'],
            'center_id'  => $data['center']->id,
            'team_year'  => $data['teamYear'],
            'reg_date'   => $data['regDate'],
        ]);

        if (isset($data['email'])) {
            $application->person->email = $data['email'];
        }
        if (isset($data['phone'])) {
            $application->person->phone = $data['phone'];
        }
        if (isset($data['isReviewer'])) {
            $application->isReviewer = $data['isReviewer'];
        }

        if ($application->person->isDirty()) {
            $application->person->save();
        }
        $application->save();

        return $application->load('person');
    }

    public function update(Models\TmlpRegistration $application, array $data)
    {
        $data = $this->parseInputs($data);

        foreach ($data as $property => $value) {
            if ($this->validProperties[$property]['owner'] == 'application') {
                if ($application->$property !== $value) {
                    $application->$property = $value;
                }
            }
            if ($this->validProperties[$property]['owner'] == 'person') {
                if ($application->person->$property !== $value) {
                    $application->person->$property = $value;
                }
            }
        }

        if ($application->person->isDirty()) {
            $application->person->save();
        }
        if ($application->isDirty()) {
            $application->save();
        }

        return $application->load('person');
    }

    public function getWeekData(Models\TmlpRegistration $application, Carbon $reportingDate)
    {
        $cached = $this->checkCache(compact('application', 'reportingDate'));
        if ($cached) {
            return $cached;
        }

        $report = LocalReport::getStatsReport($application->center, $reportingDate);

        $response = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $application->id,
            'stats_report_id'      => $report->id,
        ])->load('registration.person', 'incomingQuarter', 'statsReport', 'withdrawCode', 'committedTeamMember.person');

        $this->putCache($response);

        return $response;
    }

    public function setWeekData(Models\TmlpRegistration $application, Carbon $reportingDate, array $data)
    {
        $data = $this->parseInputs($data);

        $report = LocalReport::getStatsReport($application->center, $reportingDate);

        $applicationData = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $application->id,
            'stats_report_id'      => $report->id,
        ]);

        foreach ($data as $property => $value) {
            if ($this->validProperties[$property]['owner'] == 'applicationData') {
                if (($applicationData->$property instanceof Carbon) && Carbon::parse($value)
                                                                             ->ne($applicationData->$property)
                ) {
                    $applicationData->$property = Carbon::parse($value)->startOfDay();
                } else if ($applicationData->$property !== $value) {
                    $applicationData->$property = $value;
                }
            } else if ($property === 'regDate') {
                if ($applicationData->$property !== $value || $application->$property !== $value) {
                    $applicationData->$property = $value;
                    $application->$property = $value;
                    $application->save();
                }
            }
        }

        if (!$applicationData->statsReportId) {
            $applicationData->statsReportId = $report->id;
        }

        if ($applicationData->regDate != $application->regDate) {
            $applicationData->regDate = $application->regDate;
        }

        if ($applicationData->isDirty()) {
            $applicationData->save();
        }

        return $applicationData->load('registration.person', 'incomingQuarter', 'statsReport', 'withdrawCode', 'committedTeamMember.person');
    }
}


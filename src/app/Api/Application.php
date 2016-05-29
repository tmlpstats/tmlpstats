<?php namespace TmlpStats\Api;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Domain;

/**
 * Applications
 */
class Application extends ApiBase
{
    public function create(array $data)
    {
        $input = Domain\TeamApplication::fromArray($data, ['firstName', 'lastName', 'center', 'teamYear', 'regDate']);

        $application = Models\TmlpRegistration::firstOrNew([
            'first_name' => $input->firstName,
            'last_name' => $input->lastName,
            'center_id' => $input->center->id,
            'team_year' => $input->teamYear,
            'reg_date' => $input->regDate,
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
        $teamApp = Domain\TeamApplication::fromArray($data);
        $teamApp->fillModel(null, $application);

        if ($application->person->isDirty()) {
            $application->person->save();
        }
        if ($application->isDirty()) {
            $application->save();
        }

        return $application->load('person');
    }

    public function allForCenter(Models\Center $center, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($center);
        } else if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            $dateStr = $reportingDate->toDateString();
            throw new ApiExceptions\BadRequestException('Reporting date must be a Friday.');
        }

        $quarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);

        $reports = Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->official()
            ->where('reporting_date', '<=', $reportingDate)
            ->orderBy('reporting_date', 'asc')
            ->with('tmlpRegistrationData')
            ->get();

        $allApplications = [];

        foreach ($reports as $report) {
            foreach ($report->tmlpRegistrationData as $app) {
                // Store indexed here so we end up with only the most recent one for each application
                //$data = $this->getWeekData($app->registration, $report->reportingDate);
                $allApplications[$app->registration->id] = Domain\TeamApplication::fromModel($app);
            }
        }

        usort($allApplications, function ($a, $b) {
            return strcmp($a->firstName, $b->firstName);
        });

        return array_values($allApplications);
    }

    public function getWeekData(Models\TmlpRegistration $application, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($application->center);
        } else if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            $dateStr = $reportingDate->toDateString();
            throw new ApiExceptions\BadRequestException('Reporting date must be a Friday.');
        }

        $getUnsubmitted = $reportingDate->gte(Carbon::now($application->center->timezone)->startOfDay());

        $report = LocalReport::getStatsReport($application->center, $reportingDate, $getUnsubmitted);

        $response = Models\TmlpRegistrationData::firstOrNew([
            'tmlp_registration_id' => $application->id,
            'stats_report_id' => $report->id,
        ]);

        // If we're creating a new data object now, pre-populate it with data from last week
        if (!$response->exists) {

            $lastWeeksReport = Models\StatsReport::byCenter($application->center)
                ->reportingDate($reportingDate->copy()->subWeek())
                ->official()
                ->first();

            // It's the center's first official report or they didn't submit last week
            $lastWeeksData = null;
            if ($lastWeeksReport) {
                $lastWeeksData = Models\TmlpRegistrationData::byStatsReport($lastWeeksReport)
                    ->ByRegistration($application)
                    ->first();
            }

            if ($lastWeeksData) {
                $response->mirror($lastWeeksData);
            }

            $response->save();
        }

        return $response->load('registration.person', 'incomingQuarter', 'statsReport', 'withdrawCode', 'committedTeamMember.person');
    }

    public function setWeekData(Models\TmlpRegistration $application, Carbon $reportingDate, array $data)
    {
        if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            $dateStr = $reportingDate->toDateString();
            throw new ApiExceptions\BadRequestException('Reporting date must be a Friday.');
        }

        $report = LocalReport::getStatsReport($application->center, $reportingDate);

        $applicationData = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $application->id,
            'stats_report_id' => $report->id,
        ]);

        if (!$applicationData->statsReportId) {
            $applicationData->statsReportId = $report->id;
        }
        $teamApp = Domain\TeamApplication::fromModel($applicationData, $application);
        $teamApp->tmlpRegistrationId = $application->id;
        $teamApp->clearSetValues();

        // Now insert our newly changed data, validating and coercing too
        $teamApp->fillFromArray($data);
        $teamApp->fillModel($applicationData, $application);

        $applicationData->save();
        $application->save();

        return $teamApp;
    }
}

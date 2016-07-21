<?php
namespace TmlpStats\Api;

use App;
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

        // Create only creates
        if ($application->exists) {
            throw new ApiExceptions\BadRequestException('Application already exists');
        }

        if ($input->has('email')) {
            $application->person->email = $input->email;
        }
        if ($input->has('phone')) {
            $application->person->phone = $input->phone;
        }
        if ($input->has('isReviewer')) {
            $application->isReviewer = $input->isReviewer;
        }

        $application->person->save();
        $application->save();

        // Make sure we have a data object for the new course so we can get it's data later
        $this->getWeekData($application);

        return $application->load('person');
    }

    public function update(Models\TmlpRegistration $application, array $data)
    {
        $teamApp = Domain\TeamApplication::fromArray($data);
        $teamApp->fillModel(null, $application);

        $application->person->save();
        $application->save();

        return $application->load('person');
    }

    public function allForCenter(Models\Center $center, $includeInProgress = false, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($center);
        } else if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
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
                $allApplications[$app->tmlpRegistrationId] = Domain\TeamApplication::fromModel($app);
            }
        }

        // Pick up any applications that are new this week
        $thisReport = LocalReport::getStatsReport($center, $reportingDate, true);
        foreach ($thisReport->tmlpRegistrationData() as $app) {
            if (isset($allApplications[$app->tmlpRegistrationId])) {
                continue;
            }

            $allApplications[$app->tmlpRegistrationId] = Domain\TeamApplication::fromModel($app);
        }

        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\TeamApplication::class);
            foreach ($found as $app) {
                $allApplications[$app->tmlpRegistrationId] = $app;
            }
        }

        usort($allApplications, function ($a, $b) {
            if ($a->firstName === $b->firstName) {
                return strcmp($a->lastName, $b->lastName);
            }

            return strcmp($a->firstName, $b->firstName);
        });

        return array_values($allApplications);
    }

    public function getWeekData(Models\TmlpRegistration $application, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($application->center);
        } else if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
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

    /**
     * Stash information about a registration (combined name data and application progress data) to be used for later validation.
     * @param  Center  $center         The courses's center
     * @param  Carbon  $reportingDate  Reporting date
     * @param  array   $data           Information to use to construct a TeamApplication.
     */
    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        $submissionData = App::make(SubmissionData::class);
        $appId = array_get($data, 'id', null);
        if (is_numeric($appId)) {
            $appId = intval($appId);
        }

        if ($appId !== null && $appId > 0) {
            $application = Models\TmlpRegistration::findOrFail($appId);
            $teamApp = Domain\TeamApplication::fromModel(null, $application);
            $teamApp->updateFromArray($data);
        } else {
            if (!$appId) {
                $appId = $submissionData->generateId();
                $data['id'] = $appId;
            }
            $teamApp = Domain\TeamApplication::fromArray($data);
        }
        $submissionData->store($center, $reportingDate, $teamApp);

        $report = LocalReport::getStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $teamApp, $appId);

        return [
            'success' => true,
            'storedId' => $appId,
            'valid' => $validationResults['valid'],
            'messages' => $validationResults['messages'],
        ];
    }

    /**
     * Commit week data to the database. Will be performed during validation to write the domain object into the DB
     * @param  Models\TmlpRegistration $application   The application we are working with.
     * @param  Carbon                  $reportingDate [description]
     * @param  Domain\TeamApplication  $data          [description]
     */
    public function commitStashedApp(Models\TmlpRegistration $application, Carbon $reportingDate, Domain\TeamApplication $data)
    {
        $report = LocalReport::getStatsReport($application->center, $reportingDate);

        $applicationData = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $application->id,
            'stats_report_id' => $report->id,
        ]);

        // TODO any domain specific validation?

        $teamApp->fillModel($applicationData, $application);

        $applicationData->save();
        $application->save();

        return $teamApp;
    }
}

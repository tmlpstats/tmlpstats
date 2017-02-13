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

        return $application->load('person');
    }

    public function allForCenter(Models\Center $center, Carbon $reportingDate = null, $includeInProgress = false)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($center);
        } else {
            App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);
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
        $thisReport = LocalReport::ensureStatsReport($center, $reportingDate, true);
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
                $app->meta['localChanges'] = true;
                $allApplications[$app->id] = $app;
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

    /**
     * Stash information about a registration (combined name data and application progress data) to be used for later validation.
     * @param  Center  $center         The courses's center
     * @param  Carbon  $reportingDate  Reporting date
     * @param  array   $data           Information to use to construct a TeamApplication.
     */
    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $submissionData = App::make(SubmissionData::class);
        $appId = array_get($data, 'id', null);
        if (is_numeric($appId)) {
            $appId = intval($appId);
        }

        if ($appId !== null && $appId > 0) {
            $application = Models\TmlpRegistration::findOrFail($appId);
            $teamApp = Domain\TeamApplication::fromModel(null, $application);
            $teamApp->updateFromArray($data, ['incomingQuarter']);
        } else {
            if (!$appId) {
                $appId = $submissionData->generateId();
                $data['id'] = $appId;
            }
            $teamApp = Domain\TeamApplication::fromArray($data);
        }
        $report = LocalReport::ensureStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $teamApp, $appId);

        if ($appId > 0 || $validationResults['valid']) {
            $submissionData->store($center, $reportingDate, $teamApp);
        } else {
            return [
                'success' => false,
                'valid' => $validationResults['valid'],
                'messages' => $validationResults['messages'],
            ];
        }

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
        $report = LocalReport::ensureStatsReport($application->center, $reportingDate);

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

    /**
     * Return a list of valid CenterQuarters that someone can register into.
     * @param  Models\Center  $center        The center we care about
     * @param  Carbon         $reportingDate The current reporting date to use as reference.
     * @param  Models\Quarter $startQuarter  If provided, a reference start quarter to help prevent lookups.
     * @return array<Domain\CenterQuarter>
     */
    public function validRegistrationQuarters(Models\Center $center, Carbon $reportingDate, Models\Quarter $startQuarter = null)
    {
        if ($startQuarter == null) {
            $startQuarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);
        }

        // Probably not needed, but might as well, for looking at previous quarters.
        while ($reportingDate->gt($startQuarter->getQuarterEndDate($center))) {
            $startQuarter = $startQuarter->getNextQuarter();
        }

        $next1 = $startQuarter->getNextQuarter();
        $next2 = $next1->getNextQuarter();

        $quarters = [
            Domain\CenterQuarter::ensure($center, $next1),
            Domain\CenterQuarter::ensure($center, $next2),
        ];

        // In the last 2 weeks of the quarter, we can also register into the next-next quarter.
        if ($startQuarter->getQuarterEndDate($center)->copy()->subWeeks(2)->lt($reportingDate)) {
            $quarters[] = Domain\CenterQuarter::ensure($center, $next2->getNextQuarter());
        }

        return $quarters;
    }

    public function getUnchangedFromLastReport(Models\Center $center, Carbon $reportingDate)
    {
        $results = [];

        $allData = $this->allForCenter($center, $reportingDate, true);
        foreach ($allData as $dataObject) {
            if (!array_get($dataObject->meta, 'localChanges', false)) {
                $results[] = $dataObject;
            }
        }

        return $results;
    }

    public function getChangedFromLastReport(Models\Center $center, Carbon $reportingDate)
    {
        $collection = App::make(SubmissionData::class)->allForType($center, $reportingDate, Domain\TeamApplication::class);

        return array_flatten($collection->getDictionary());
    }

}

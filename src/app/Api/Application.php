<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions;
use TmlpStats\Api\Traits;
use TmlpStats\Domain;

/**
 * Applications
 */
class Application extends ApiBase
{
    use Traits\UsesReportDates, Traits\ValidatesObjects;

    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $allApplications = [];
        $deleted = collect([]);
        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            list($found, $deleted) = $submissionData->allForType($center, $reportingDate, Domain\TeamApplication::class, ['include_deleted' => true]);
            foreach ($found as $domain) {
                $allApplications[$domain->id] = $domain;
                $domain->meta['localChanges'] = true;
                $domain->meta['canDelete'] = true;
            }

            $deleted = $deleted->map(function ($x) {return intval($x);})->flip();
        }

        $lastReport = $this->relevantReport($center, $reportingDate);
        if ($lastReport) {
            $applications = App::make(LocalReport::class)->getApplicationsList($lastReport, ['returnUnprocessed' => true]);
            foreach ($applications as $appData) {
                if ($deleted->has($appData->tmlpRegistrationId)) {
                    continue;
                } else if ($domain = array_get($allApplications, $appData->tmlpRegistrationId)) {
                    // it's a small optimization, but prevent creating domain if we have an existing SubmissionData version
                    $domain->meta['canDelete'] = false;
                    continue;
                }

                $domain = Domain\TeamApplication::fromModel($appData, $appData->tmlpRegistration);
                $domain->meta['fromReport'] = true;
                $allApplications[$domain->id] = $domain;
            }
        }

        return $allApplications;
    }

    /**
     * Stash information about a registration (combined name data and application progress data) to be used for later validation.
     * @param  Center  $center         The app's center
     * @param  Carbon  $reportingDate  Reporting date
     * @param  array   $data           Information to use to construct a TeamApplication.
     */
    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate, ['write']);
        $this->assertCan('submitStats', $center);

        if (array_get($data, 'action', '') == 'delete') {
            return $this->deleteApp($center, $reportingDate, $data['id']);
        }

        $submissionData = App::make(SubmissionData::class);
        $appId = $submissionData->numericStorageId($data, 'id');

        $pastWeeks = [];

        if ($appId !== null && $appId > 0) {
            $application = Models\TmlpRegistration::findOrFail($appId);
            $teamApp = Domain\TeamApplication::fromModel(null, $application);
            $teamApp->updateFromArray($data, ['incomingQuarter']);

            $pastWeeks = $this->getPastWeeksData($center, $reportingDate, $application);
        } else {
            $teamApp = Domain\TeamApplication::fromArray($data);
        }

        $report = LocalReport::ensureStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $teamApp, $appId, $pastWeeks);

        if (!isset($data['_idGenerated']) || $validationResults['valid']) {
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

    protected function deleteApp(Models\Center $center, Carbon $reportingDate, $appId)
    {
        $appId = intval($appId);
        // Cannot override delete for an application with an actual ID
        if ($appId > 0 && !$this->context->can('overrideDelete', $center)) {
            throw new Exceptions\UnauthorizedException('Cannot delete this application without delete override privileges.');
        }
        App::make(SubmissionData::class)
            ->deleteOne($center, $reportingDate, Domain\TeamApplication::class, $appId);

        return [
            'success' => true,
            'valid' => true,
        ];
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

    protected function getPastWeeksData(Models\Center $center, Carbon $reportingDate, Models\TmlpRegistration $app)
    {
        $lastWeekReportingDate = $this->lastReportingDate($center, $reportingDate);
        if (!$lastWeekReportingDate) {
            return [];
        }

        $lastReport = $this->relevantReport($center, $lastWeekReportingDate);
        if (!$lastReport) {
            return [];
        }

        $lastWeekData = Models\TmlpRegistrationData::byStatsReport($lastReport)->byRegistration($app)->first();
        if (!$lastWeekData) {
            return [];
        }

        return [
            Domain\TeamApplication::fromModel($lastWeekData, $app),
        ];
    }

    public function getWeekSoFar(Models\Center $center, Carbon $reportingDate, $includeInProgress = true)
    {
        return $this->allForCenter($center, $reportingDate, $includeInProgress);
    }
}

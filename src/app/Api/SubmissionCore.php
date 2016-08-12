<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Exceptions;

class SubmissionCore extends AuthenticatedApiBase
{
    /**
     * Initialize a submission, checking if parameters are valid.
     * @param  Models\Center $center        [description]
     * @param  Carbon        $reportingDate [description]
     * @return [type]                       [description]
     */
    public function initSubmission(Models\Center $center, Carbon $reportingDate)
    {
        $this->checkCenterDate($center, $reportingDate);

        $localReport = App::make(LocalReport::class);
        $lastValidReport = $localReport->getLastStatsReportSince($center, $reportingDate);
        $team_members = $localReport->getClassList($lastValidReport);
        $withdraw_codes = Models\WithdrawCode::get();

        return [
            'success' => true,
            'lookups' => compact('withdraw_codes', 'team_members', 'center'),
        ];
    }

    public function checkCenterDate(Models\Center $center, Carbon $reportingDate)
    {
        if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            throw new Exceptions\BadRequestException('Reporting date must be a Friday.');
        }

        // TODO check reporting date is in this center's quarter and so on.

        return ['success' => true];
    }

}

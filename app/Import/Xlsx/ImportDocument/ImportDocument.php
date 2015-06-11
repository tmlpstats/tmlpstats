<?php
namespace TmlpStats\Import\Xlsx\ImportDocument;

use TmlpStats\Quarter;
use TmlpStats\Center;
use TmlpStats\StatsReport;
use TmlpStats\Message;
use TmlpStats\Util;
use TmlpStats\TmlpRegistration;

use TmlpStats\Import\Xlsx\DataImporter\DataImporterFactory;
use Carbon\Carbon;

use Auth;
use DB;

class ImportDocument extends ImportDocumentAbstract
{
    protected $importers = array();

    // TODO: some of this validation logic belongs in the Validator
    protected function validateReport()
    {
        $isValid = true;
        if (!$this->validateStatsReport()) {
            $isValid = false;
        }
        foreach ($this->importers as $type => $object) {

            $method = 'validate' . ucfirst($type);
            if (method_exists($this, $method)) {
                if(!$this->$method()) {
                    $isValid = false;
                }
            }
        }
        return $isValid;
    }

    protected function validateClassList()
    {
        $isValid = true;

        $list = DB::table('team_members')
                    ->join('team_members_data', 'team_members.id', '=', 'team_members_data.team_member_id')
                    ->where('team_members_data.stats_report_id', '=', $this->statsReport->id)
                    ->orderBy('offset', 'asc')
                    ->get();

        foreach ($list as $dataArray) {

            $data = Util::objectToCamelCase($dataArray);
            $validator = $this->getValidator('classList');
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
        }
        return $isValid;
    }

    protected function validateContactInfo()
    {
        $isValid = true;

        $list = DB::table('program_team_members')
                    ->where('program_team_members.center_id', '=', $this->statsReport->center->id)
                    ->orderBy('offset', 'asc')
                    ->get();

        foreach ($list as $dataArray) {

            $data = Util::objectToCamelCase($dataArray);
            $validator = $this->getValidator('contactInfo');
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
        }
        return $isValid;
    }

    protected function validateCommCourseInfo()
    {
        $isValid = true;

        $list = DB::table('courses')
                    ->join('courses_data', 'courses.id', '=', 'courses_data.course_id')
                    ->where('courses_data.stats_report_id', '=', $this->statsReport->id)
                    ->orderBy('offset', 'asc')
                    ->get();

        foreach ($list as $dataArray) {

            $data = Util::objectToCamelCase($dataArray);
            $validator = $this->getValidator('commCourseInfo');
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
        }
        return $isValid;
    }

    protected function validateTmlpGameInfo()
    {
        $isValid = true;

        $list = DB::table('tmlp_games')
                    ->join('tmlp_games_data', 'tmlp_games.id', '=', 'tmlp_games_data.tmlp_game_id')
                    ->where('tmlp_games_data.stats_report_id', '=', $this->statsReport->id)
                    ->orderBy('offset', 'asc')
                    ->get();

        $qStartCurrentTeam1Registered = 0;
        $qStartFutureTeam1Registered  = 0;
        $qStartCurrentTeam1Approved   = 0;
        $qStartFutureTeam1Approved    = 0;

        $currentTeam1Registered       = 0;
        $futureTeam1Registered        = 0;
        $currentTeam1Approved         = 0;
        $futureTeam1Approved          = 0;

        $qStartCurrentTeam2Registered = 0;
        $qStartFutureTeam2Registered  = 0;
        $qStartCurrentTeam2Approved   = 0;
        $qStartFutureTeam2Approved    = 0;

        $currentTeam2Registered       = 0;
        $futureTeam2Registered        = 0;
        $currentTeam2Approved         = 0;
        $futureTeam2Approved          = 0;

        $t1Registrations = TmlpRegistration::team1Incoming()->center($this->center)->get();
        foreach ($t1Registrations as $registration) {
            $data = $registration->registrationData()->reportingDate($this->statsReport->reportingDate)->first();

            if (!$data) {
                continue;
            }

            if ($data->incomingWeekend == 'current') {

                if ($registration->regDate->lte($this->quarter->startWeekendDate)) {
                    $qStartCurrentTeam1Registered++;
                }
                $currentTeam1Registered++;
            } else {

                if ($registration->regDate->lte($this->quarter->startWeekendDate)) {
                    $qStartFutureTeam1Registered++;
                }
                $futureTeam1Registered++;
            }

            if ($data->apprDate) {
                if ($data->incomingWeekend == 'current') {

                    if ($data->apprDate->lte($this->quarter->startWeekendDate)) {
                        $qStartCurrentTeam1Approved++;
                    }
                    if ($data->appr) {
                        $currentTeam1Approved++;
                    }
                } else {

                    if ($data->apprDate->lte($this->quarter->startWeekendDate)) {
                        $qStartFutureTeam1Approved++;
                    }
                    if ($data->appr) {
                        $futureTeam1Approved++;
                    }
                }
            }
        }

        $t2Registrations = TmlpRegistration::team2Incoming()->center($this->center)->get();
        foreach ($t2Registrations as $registration) {
            $data = $registration->registrationData()->reportingDate($this->statsReport->reportingDate)->first();

            if (!$data) {
                continue;
            }

            if ($data->incomingWeekend == 'current') {

                if ($registration->regDate->lte($this->quarter->startWeekendDate)) {
                    $qStartCurrentTeam2Registered++;
                }
                $currentTeam2Registered++;
            } else {

                if ($registration->regDate->lte($this->quarter->startWeekendDate)) {
                    $qStartFutureTeam2Registered++;
                }
                $futureTeam2Registered++;
            }

            if ($data->apprDate) {
                if ($data->incomingWeekend == 'current') {

                    if ($data->apprDate->lte($this->quarter->startWeekendDate)) {
                        $qStartCurrentTeam2Approved++;
                    }
                    if ($data->appr) {
                        $currentTeam2Approved++;
                    }
                } else {

                    if ($data->apprDate->lte($this->quarter->startWeekendDate)) {
                        $qStartFutureTeam2Approved++;
                    }
                    if ($data->appr) {
                        $futureTeam2Approved++;
                    }
                }
            }
        }

        $quarterStart = $this->quarter->startWeekendDate;
        $firstWeekDate = $quarterStart->addDays(7);

        $qStartRegisteredTotalT1 = 0;
        $qStartApprovedTotalT1   = 0;
        $qStartRegisteredTotalT2 = 0;
        $qStartApprovedTotalT2   = 0;

        foreach ($list as $dataArray) {

            $data = Util::objectToCamelCase($dataArray);
            $validator = $this->getValidator('tmlpCourseInfo');
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());

            $game = '';
            switch ($data->type) {

                case 'Incoming T1':
                    $reportedRegistered      = $data->quarterStartRegistered;
                    $reportedApproved        = $data->quarterStartApproved;
                    $calculatedRegistered    = $qStartCurrentTeam1Registered;
                    $calculatedApproved      = $qStartCurrentTeam1Approved;

                    $qStartRegisteredTotalT1 += $reportedRegistered;
                    $qStartApprovedTotalT1   += $reportedApproved;
                    break;
                case 'Future T1':
                    $reportedRegistered      = $data->quarterStartRegistered;
                    $reportedApproved        = $data->quarterStartApproved;
                    $calculatedRegistered    = $qStartFutureTeam1Registered;
                    $calculatedApproved      = $qStartFutureTeam1Approved;

                    $qStartRegisteredTotalT1 += $reportedRegistered;
                    $qStartApprovedTotalT1   += $reportedApproved;
                    break;
                case 'Incoming T2':
                    $reportedRegistered      = $data->quarterStartRegistered;
                    $reportedApproved        = $data->quarterStartApproved;
                    $calculatedRegistered    = $qStartCurrentTeam2Registered;
                    $calculatedApproved      = $qStartCurrentTeam2Approved;

                    $qStartRegisteredTotalT2 += $reportedRegistered;
                    $qStartApprovedTotalT2   += $reportedApproved;
                    break;
                case 'Future T2':
                    $reportedRegistered      = $data->quarterStartRegistered;
                    $reportedApproved        = $data->quarterStartApproved;
                    $calculatedRegistered    = $qStartFutureTeam2Registered;
                    $calculatedApproved      = $qStartFutureTeam2Approved;

                    $qStartRegisteredTotalT2 += $reportedRegistered;
                    $qStartApprovedTotalT2   += $reportedApproved;
                    break;
            }

            // Validate Quarter starting totals on the first week
            if ($this->reportingDate->eq($firstWeekDate)) {

                if ($reportedRegistered != $calculatedRegistered) {
                    $this->messages['errors'][] = Message::create(static::TAB_COURSES)->addMessage('IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', false, $data->type, $reportedRegistered, $calculatedRegistered);
                    $isValid = false;
                }

                if ($reportedApproved != $calculatedApproved) {
                    $this->messages['errors'][] = Message::create(static::TAB_COURSES)->addMessage('IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', false, $data->type, $reportedApproved, $calculatedApproved);
                    $isValid = false;
                }
            }
        }

        // TODO: Turning this off for now because it's a bit more complicated to calculate these properly.
        // Validate Quarter starting totals on mid quarter weeks. (Registrations may move between current and future)
        if ($this->reportingDate->ne($firstWeekDate)) {

            $totals = $qStartCurrentTeam1Registered + $qStartFutureTeam1Registered;
            if ($qStartRegisteredTotalT1 != $totals) {
                $this->messages['warnings'][] = Message::create(static::TAB_COURSES)->addMessage('IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', false, $qStartRegisteredTotalT1, $totals);
            }

            $totals = $qStartCurrentTeam1Approved + $qStartFutureTeam1Approved;
            if ($qStartApprovedTotalT1 != $totals) {
                $this->messages['warnings'][] = Message::create(static::TAB_COURSES)->addMessage('IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', false, $qStartApprovedTotalT1, $totals);
            }

            $totals = $qStartCurrentTeam2Registered + $qStartFutureTeam2Registered;
            if ($qStartRegisteredTotalT2 != $totals) {
                $this->messages['warnings'][] = Message::create(static::TAB_COURSES)->addMessage('IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', false, $qStartRegisteredTotalT2, $totals);
            }

            $totals = $qStartCurrentTeam2Approved + $qStartFutureTeam2Approved;
            if ($qStartApprovedTotalT2 != $totals) {
                $this->messages['warnings'][] = Message::create(static::TAB_COURSES)->addMessage('IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', false, $qStartApprovedTotalT2, $totals);
            }
        }

        return $isValid;
    }

    protected function validateTmlpRegistration()
    {
        $isValid = true;

        $list = DB::table('tmlp_registrations')
                    ->join('tmlp_registrations_data', 'tmlp_registrations.id', '=', 'tmlp_registrations_data.tmlp_registration_id')
                    ->where('tmlp_registrations_data.stats_report_id', '=', $this->statsReport->id)
                    ->orderBy('offset', 'asc')
                    ->get();

        foreach ($list as $dataArray) {

            $data = Util::objectToCamelCase($dataArray);
            $validator = $this->getValidator('tmlpRegistration');
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
        }
        return $isValid;
    }

    protected function validateCenterStats()
    {
        $isValid = true;
        $thisWeekActual = null;

        // Actuals
        $list = DB::table('center_stats')
                    ->join('center_stats_data', 'center_stats.actual_data_id', '=', 'center_stats_data.id')
                    ->where('center_stats_data.center_id', '=', $this->center->id)
                    ->where('center_stats_data.stats_report_id', '=', $this->statsReport->id)
                    ->orderBy('offset', 'asc')
                    ->get();

        foreach ($list as $dataArray) {

            $data = Util::objectToCamelCase($dataArray);
            $validator = $this->getValidator('centerStats');
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
            if ($data->reportingDate == $this->statsReport->reportingDate->toDateString()) {
                $thisWeekActual = $data;
            }
        }

        // Promises
        $list = DB::table('center_stats')
                    ->join('center_stats_data', 'center_stats.promise_data_id', '=', 'center_stats_data.id')
                    ->where('center_stats_data.center_id', '=', $this->center->id)
                    ->where('center_stats_data.stats_report_id', '=', $this->statsReport->id)
                    ->orderBy('offset', 'asc')
                    ->get();

        foreach ($list as $dataArray) {

            $data = Util::objectToCamelCase($dataArray);
            $validator = $this->getValidator('centerStats');
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
        }

        // GITW and TDO
        $teamMemberStats = DB::table('team_members_data')
                                ->select(DB::raw("COUNT(*) as activeMemberCount"))
                                ->addSelect(DB::raw("SUM(IF(gitw = 'E', 1, 0)) as effectiveCount"))
                                ->addSelect(DB::raw("SUM(IF(tdo = 'Y', 1, 0)) as tdoCount"))
                                ->where('stats_report_id', '=', $this->statsReport->id)
                                ->whereNull('wd')
                                ->whereNull('wbo')
                                ->whereNull('xfer_out')
                                ->first();

        $gitwGame = round(((int)$teamMemberStats->effectiveCount/(int)$teamMemberStats->activeMemberCount) * 100);
        $tdoGame = round(((int)$teamMemberStats->tdoCount/(int)$teamMemberStats->activeMemberCount) * 100);

        // CAP Game
        $capStats = DB::table('courses')
                        ->select(DB::raw("SUM(courses_data.quarter_start_ter) as qStartTer"))
                        ->addSelect(DB::raw("SUM(courses_data.quarter_start_standard_starts) as qStartStandardStarts"))
                        ->addSelect(DB::raw("SUM(courses_data.current_ter) as currentTer"))
                        ->addSelect(DB::raw("SUM(courses_data.current_standard_starts) as currentStandardStarts"))
                        ->join('courses_data', 'courses.id', '=', 'courses_data.course_id')
                        ->where('courses_data.stats_report_id', '=', $this->statsReport->id)
                        ->where('courses.type', '=', 'CAP')
                        ->first();

        $capGame = (int)$capStats->currentStandardStarts - (int)$capStats->qStartStandardStarts;

        // CPC Game
        $cpcStats = DB::table('courses')
                        ->select(DB::raw("SUM(courses_data.quarter_start_ter) as qStartTer"))
                        ->addSelect(DB::raw("SUM(courses_data.quarter_start_standard_starts) as qStartStandardStarts"))
                        ->addSelect(DB::raw("SUM(courses_data.current_ter) as currentTer"))
                        ->addSelect(DB::raw("SUM(courses_data.current_standard_starts) as currentStandardStarts"))
                        ->join('courses_data', 'courses.id', '=', 'courses_data.course_id')
                        ->where('courses_data.stats_report_id', '=', $this->statsReport->id)
                        ->where('courses.type', '=', 'CPC')
                        ->first();

        $cpcGame = (int)$cpcStats->currentStandardStarts - (int)$cpcStats->qStartStandardStarts;

        // T1x and T2x Games
        $startWeekendDate = $this->quarter->startWeekendDate->toDateString();
        $tmlpRegCurrent = DB::table('tmlp_registrations_data')
                        ->select(DB::raw("SUM(IF(appr = '1', 1, 0)) as team1Approved"))
                        ->addSelect(DB::raw("SUM(IF((appr = '2' OR appr = 'R'), 1, 0)) as team2Approved"))
                        ->where('tmlp_registrations_data.stats_report_id', '=', $this->statsReport->id)
                        ->first();

        $tmlpRegQStart = DB::table('tmlp_games')
                        ->select(DB::raw("SUM(IF(tmlp_games.type LIKE '%T1', tmlp_games_data.quarter_start_approved, 0)) as team1Approved"))
                        ->addSelect(DB::raw("SUM(IF(tmlp_games.type LIKE '%T2', tmlp_games_data.quarter_start_approved, 0)) as team2Approved"))
                        ->join('tmlp_games_data', 'tmlp_games.id', '=', 'tmlp_games_data.tmlp_game_id')
                        ->where('tmlp_games_data.stats_report_id', '=', $this->statsReport->id)
                        ->first();

        $t1xGame = $tmlpRegCurrent->team1Approved - $tmlpRegQStart->team1Approved;
        $t2xGame = $tmlpRegCurrent->team2Approved - $tmlpRegQStart->team2Approved;

        // Make sure they match
        if ($thisWeekActual) {

            if ($thisWeekActual->cap != $capGame) {
                $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_CAP_ACTUAL_INCORRECT', false, $thisWeekActual->cap, $capGame);
                $isValid = false;
            }

            if ($thisWeekActual->cpc != $cpcGame) {
                $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_CPC_ACTUAL_INCORRECT', false, $thisWeekActual->cpc, $cpcGame);
                $isValid = false;
            }

            if ($thisWeekActual->t1x != $t1xGame) {
                // This is a warning since the regional is asked to verify
                $this->messages['warnings'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_T1X_ACTUAL_INCORRECT', false, $thisWeekActual->t1x, $t1xGame);
            }

            if ($thisWeekActual->t2x != $t2xGame) {
                $this->messages['warnings'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_T2X_ACTUAL_INCORRECT', false, $thisWeekActual->t2x, $t2xGame);
            }

            if ($thisWeekActual->gitw != $gitwGame) {
                $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_GITW_ACTUAL_INCORRECT', false, $thisWeekActual->gitw, $gitwGame);
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function validateStatsReport()
    {
        $isValid = true;

        if ($this->enforceVersion && $this->statsReport->spreadsheetVersion != $this->center->sheetVersion) {

            $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_SPREADSHEET_VERSION_MISMATCH', false, $this->statsReport->spreadsheetVersion, $this->center->sheetVersion);
            $isValid = false;
        }

        if ($this->expectedDate && $this->expectedDate->ne($this->statsReport->reportingDate)) {

            if ($this->statsReport->reportingDate->diffInDays($this->statsReport->quarter->endWeekendDate) < 7) {
                // Reporting in the last week of quarter
                if ($this->statsReport->reportingDate->ne($this->statsReport->quarter->endWeekendDate)) {
                    $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_SPREADSHEET_DATE_MISMATCH_LAST_WEEK', false, $this->statsReport->reportingDate->toDateString(), $this->statsReport->quarter->endWeekendDate->toDateString());
                    $isValid = false;
                }
            } else {
                $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_SPREADSHEET_DATE_MISMATCH', false, $this->statsReport->reportingDate->toDateString(), $this->expectedDate->toDateString());
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function process()
    {
        $this->loadCenter();
        $this->loadDate();
        $this->loadQuarter();

        if (!$this->isValid()) {
            // Stop processing. We can't find center or reporting date.
            return;
        }

        // TODO: add option for someone to update validated sheets.
        $this->statsReport = StatsReport::firstOrCreate(array(
            'center_id'           => $this->center->id,
            'quarter_id'          => $this->quarter->id,
            'reporting_date'      => $this->reportingDate->toDateString(),
        ));
        $this->statsReport->spreadsheetVersion = $this->version;
        $this->statsReport->userId = Auth::user()->id;
        $this->statsReport->save();
        $this->statsReport->clear(); // Make sure there aren't any left over artifacts from the last run

        // Order matters here. TmlpRegistrations and ContactInfo search for team members
        // so ClassList must be loaded first
        $this->processWeeklyStats();
        $this->processClassList();
        $this->processCourseInfo();
        $this->processContactInfo();
        $this->processTmlpRegistrations();
    }

    protected function loadCenter()
    {
        $data = $this->getWeeklyStatsSheet();

        $centerName = $data[1]['G'];

        $this->center = Center::name($centerName)->first();
        if (!$this->center) {
            $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_CENTER_NOT_FOUND', false, $centerName);
        } else if (!$this->center->active) {
            $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_CENTER_INACTIVE', false, $centerName);
        }
    }
    protected function loadDate()
    {
        $data = $this->getWeeklyStatsSheet();

        $reportingDate = $data[2]['A'];

        $this->reportingDate = Util::getExcelDate($reportingDate);

        if (!$this->reportingDate) {
            // Parse international dates properly
            $this->reportingDate = Util::parseUnknownDateFormat($reportingDate);
            $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_DATE_FORMAT_INCORRECT', false, $reportingDate);
        }

        if (!$this->reportingDate || $this->reportingDate->lt(Carbon::create(1980,1,1,0,0,0))) {
            $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_DATE_NOT_FOUND', false, $reportingDate);
        }
    }
    protected function loadVersion()
    {
        if ($this->version === null) {

            $data = $this->getWeeklyStatsSheet();

            $version = $data[2]['L'];

            if (!preg_match("/^V((\d+\.\d+)(\.\d+)?)$/i", $version, $matches)) {
                $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_VERSION_FORMAT_INCORRECT', false, $version);
            } else {
                $this->version = $matches[1]; // only grab to num
            }
        }
    }
    protected function loadQuarter()
    {
        if (!$this->center || !$this->reportingDate) {
            // Don't try to load the quarter without a center.
            // No need to throw error, one has already been logged
            return;
        }
        $this->quarter = Quarter::findByDateAndRegion($this->reportingDate, $this->center->globalRegion);
        if (!$this->quarter) {
            $this->messages['errors'][] = Message::create(static::TAB_WEEKLY_STATS)->addMessage('IMPORTDOC_QUARTER_NOT_FOUND', false, $this->reportingDate->toDateString());
        }
    }

    protected function processWeeklyStats()
    {
        $sheet = $this->getWeeklyStatsSheet();
        $importer = $this->getCenterStatsImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['centerStats'] = $importer;
    }

    protected function processTmlpRegistrations()
    {
        $sheet = $this->getWeeklyStatsSheet();
        $importer = $this->getTmlpRegistrationImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['tmlpRegistration'] = $importer;
    }

    protected function processClassList()
    {
        $sheet = $this->getClassListSheet();
        $importer = $this->getClassListImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['classList'] = $importer;
    }

    protected function processContactInfo()
    {
        $sheet = $this->getContactInfoSheet();
        $importer = $this->getContactInfoImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['contactInfo'] = $importer;
    }

    protected function processCourseInfo()
    {
        $sheet = $this->getCourseInfoSheet();
        $importer = $this->getCommCourseInfoImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['commCourseInfo'] = $importer;

        $importer = $this->getTmlpGameInfoImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['tmlpGameInfo'] = $importer;
    }

    protected function mergeMessages($messages)
    {
        foreach ($messages as $message) {

            if ($message['type'] == 'error') {
                $this->messages['errors'][] = $message;
            } else {
                $this->messages['warnings'][] = $message;
            }
        }
    }

    // This method is called after validateReport, and only if it passes.
    // Put work here that add/updates data based on valid values
    protected function postProcess()
    {
        foreach($this->importers as $name => $importer) {

            $importer->postProcess();

            // Update the Stats Report after post processing
            switch ($name) {

                case 'centerStats':
                    $centerStats = $importer->getCenterStats();
                    $this->statsReport->centerStatsId = $centerStats ? $centerStats->id : null;
                    break;

                case 'contactInfo':
                    $reportingStatistician = $importer->getReportingStatistician();
                    $this->statsReport->reportingStatisticianId = $reportingStatistician ? $reportingStatistician->id : null;
                    $this->statsReport->programManagerAttendingWeekend = $importer->getProgramManagerAttendingWeekend();
                    $this->statsReport->classroomLeaderAttendingWeekend = $importer->getClassroomLeaderAttendingWeekend();
                    break;

                default:
                    return; // No need to save
            }
            $this->statsReport->save();
        }
    }

    protected function getWeeklyStatsSheet()
    {
        return $this->loadSheet(0);
    }
    protected function getClassListSheet()
    {
        return $this->loadSheet(1);
    }
    protected function getCourseInfoSheet()
    {
        return $this->loadSheet(2);
    }
    protected function getContactInfoSheet()
    {
        return $this->loadSheet(3);
    }

    protected function getCenterStatsImporter($sheet)
    {
        return DataImporterFactory::build('CenterStats', $this->version, $sheet, $this->statsReport);
    }
    protected function getTmlpRegistrationImporter($sheet)
    {
        return DataImporterFactory::build('TmlpRegistration', $this->version, $sheet, $this->statsReport);
    }
    protected function getClassListImporter($sheet)
    {
        return DataImporterFactory::build('ClassList', $this->version, $sheet, $this->statsReport);
    }
    protected function getContactInfoImporter($sheet)
    {
        return DataImporterFactory::build('ContactInfo', $this->version, $sheet, $this->statsReport);
    }
    protected function getCommCourseInfoImporter($sheet)
    {
        return DataImporterFactory::build('CommCourseInfo', $this->version, $sheet, $this->statsReport);
    }
    protected function getTmlpGameInfoImporter($sheet)
    {
        return DataImporterFactory::build('TmlpGameInfo', $this->version, $sheet, $this->statsReport);
    }
}

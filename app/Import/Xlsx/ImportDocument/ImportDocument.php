<?php
namespace TmlpStats\Import\Xlsx\ImportDocument;

use TmlpStats\Quarter;
use TmlpStats\Center;
use TmlpStats\StatsReport;
use TmlpStats\Message;
use TmlpStats\Util;

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

        $quarterStartDate = $this->quarter->startWeekendDate->toDateString();
        $tmlpGameStats = DB::table('tmlp_registrations')
                            ->select(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '1', IF(tmlp_registrations_data.incoming_weekend = 'current', 1, 0), 0)) as currentTeam1Registered"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '1', IF(tmlp_registrations_data.incoming_weekend = 'future', 1, 0), 0)) as futureTeam1Registered"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '1', IF(tmlp_registrations_data.appr_date <= '{$quarterStartDate}', IF(tmlp_registrations_data.incoming_weekend = 'current', IF(tmlp_registrations_data.wd IS NULL, 1, 0), 0), 0), 0)) as currentTeam1Approved"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '1', IF(tmlp_registrations_data.appr_date <= '{$quarterStartDate}', IF(tmlp_registrations_data.incoming_weekend = 'future', IF(tmlp_registrations_data.wd IS NULL, 1, 0), 0), 0), 0)) as futureTeam1Approved"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '1', IF(tmlp_registrations_data.appr_date <= '{$quarterStartDate}', IF(tmlp_registrations_data.wd IS NOT NULL, 1, 0), 0), 0)) as team1Withdraws"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '2', IF(tmlp_registrations_data.incoming_weekend = 'current', 1, 0), 0)) as currentTeam2Registered"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '2', IF(tmlp_registrations_data.incoming_weekend = 'future', 1, 0), 0)) as futureTeam2Registered"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '2', IF(tmlp_registrations_data.appr_date <= '{$quarterStartDate}', IF(tmlp_registrations_data.incoming_weekend = 'current', IF(tmlp_registrations_data.wd IS NULL, 1, 0), 0), 0), 0)) as currentTeam2Approved"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '2', IF(tmlp_registrations_data.appr_date <= '{$quarterStartDate}', IF(tmlp_registrations_data.incoming_weekend = 'future', IF(tmlp_registrations_data.wd IS NULL, 1, 0), 0), 0), 0)) as futureTeam2Approved"))
                            ->addSelect(DB::raw("SUM(IF(tmlp_registrations.incoming_team_year = '2', IF(tmlp_registrations_data.appr_date <= '{$quarterStartDate}', IF(tmlp_registrations_data.wd IS NOT NULL, 1, 0), 0), 0)) as team2Withdraws"))
                            ->join('tmlp_registrations_data', 'tmlp_registrations.id', '=', 'tmlp_registrations_data.tmlp_registration_id')
                            ->where('tmlp_registrations_data.stats_report_id', '=', $this->statsReport->id)
                            ->where('tmlp_registrations.reg_date', '<=', $quarterStartDate)
                            ->first();

        $quarterStart = $this->quarter->startWeekendDate;
        $firstWeekDate = $quarterStart->addDays(7);

        $qStartRegisteredTotalT1 = 0;
        $qStartApprovedTotalT1 = 0;
        $qStartRegisteredTotalT2 = 0;
        $qStartApprovedTotalT2 = 0;

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
                    $game = 'currentTeam1';
                    $qStartRegisteredTotalT1 += $data->quarterStartRegistered;
                    $qStartApprovedTotalT1 += $data->quarterStartApproved;
                    break;
                case 'Future T1':
                    $game = 'futureTeam1';
                    $qStartRegisteredTotalT1 += $data->quarterStartRegistered;
                    $qStartApprovedTotalT1 += $data->quarterStartApproved;
                    break;
                case 'Incoming T2':
                    $game = 'currentTeam2';
                    $qStartRegisteredTotalT2 += $data->quarterStartRegistered;
                    $qStartApprovedTotalT2 += $data->quarterStartApproved;
                    break;
                case 'Future T2':
                    $game = 'futureTeam2';
                    $qStartRegisteredTotalT2 += $data->quarterStartRegistered;
                    $qStartApprovedTotalT2 += $data->quarterStartApproved;
                    break;
            }

            // Validate Quarter starting totals on the first week
            if ($this->reportingDate->eq($firstWeekDate)) {

                $registeredKey = "{$game}Registered";
                $registered = $tmlpGameStats->$registeredKey ?: 0;
                if ($data->quarterStartRegistered != $registered) {
                    $this->messages['errors'][] = Message::create('CAP & CPC Course Info.')->reportError("{$data->type} Quarter Starting Total Registered ({$data->quarterStartRegistered}) does not match the number of incoming registered before quarter start date ({$registered}).");
                    $isValid = false;
                }

                $approvedKey = "{$game}Approved";
                $approved = $tmlpGameStats->$approvedKey ?: 0;
                if ($data->quarterStartApproved != $approved) {
                    $this->messages['errors'][] = Message::create('CAP & CPC Course Info.')->reportError("{$data->type} Quarter Starting Total Approved ({$data->quarterStartApproved}) does not match the number of incoming approved before quarter start date ({$approved}).");
                    $isValid = false;
                }
            }
        }
        // Validate Quarter starting totals on mid quarter weeks. (Registrations may move between current and future)
        if ($this->reportingDate->ne($firstWeekDate)) {

            $totals = $tmlpGameStats->currentTeam1Registered + $tmlpGameStats->futureTeam1Registered;
            if ($qStartRegisteredTotalT1 != $totals) {
                $this->messages['warnings'][] = Message::create('CAP & CPC Course Info.')->reportWarning("T1 Quarter Starting Total Registered totals ({$qStartRegisteredTotalT1}) do not match the number of incoming registered before quarter start date ({$totals}). Double check what the difference is. It could be a mistake, or a transfer from another center.");
            }

            $totals = ($tmlpGameStats->currentTeam1Approved + $tmlpGameStats->futureTeam1Approved) + $tmlpGameStats->team1Withdraws;
            if ($qStartApprovedTotalT1 != $totals) {
                $this->messages['warnings'][] = Message::create('CAP & CPC Course Info.')->reportWarning("T1 Quarter Starting Total Approved totals ({$qStartApprovedTotalT1}) do not match the number of incoming approved before quarter start date ({$totals}). Double check what the difference is. It could be a mistake, or a transfer from another center.");
            }

            $totals = $tmlpGameStats->currentTeam2Registered + $tmlpGameStats->futureTeam2Registered;
            if ($qStartRegisteredTotalT2 != $totals) {
                $this->messages['warnings'][] = Message::create('CAP & CPC Course Info.')->reportWarning("T2 Quarter Starting Total Registered totals ({$qStartRegisteredTotalT2}) do not match the number of incoming registered before quarter start date ($totals). Double check what the difference is. It could be a mistake, or a transfer from another center.");
            }

            $totals = ($tmlpGameStats->currentTeam2Approved + $tmlpGameStats->futureTeam2Approved) + $tmlpGameStats->team2Withdraws;
            if ($qStartApprovedTotalT2 != $totals) {
                $this->messages['warnings'][] = Message::create('CAP & CPC Course Info.')->reportWarning("T2 Quarter Starting Total Approved totals ({$qStartApprovedTotalT2}) do not match the number of incoming approved before quarter start date ({$totals}). Double check what the difference is. It could be a mistake, or a transfer from another center.");
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
        $tmlpStats = DB::table('tmlp_registrations_data')
                        ->select(DB::raw("SUM(IF(appr = '1', 1, IF(wd LIKE '1%' AND appr_date <= '{$startWeekendDate}', -1, 0))) as team1Approved"))
                        ->addSelect(DB::raw("SUM(IF((appr = '2' OR appr = 'R'), 1, IF((wd LIKE '2%' OR wd LIKE 'R%') AND appr_date <= '{$startWeekendDate}', -1, 0))) as team2Approved"))
                        ->where('tmlp_registrations_data.stats_report_id', '=', $this->statsReport->id)
                        ->where(function($query) use ($startWeekendDate) {

                            return $query->where('appr_date', '>', $startWeekendDate)
                                         ->orWhereNotNull('wd');
                        })
                        ->first();

        $t1xGame = (int)$tmlpStats->team1Approved;
        $t2xGame = (int)$tmlpStats->team2Approved;

        // Make sure they match
        if ($thisWeekActual) {

            if ($thisWeekActual->cap != $capGame) {
                $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("CAP actual for this week ({$thisWeekActual->cap}) does not match reported value ({$capGame}).");
                $isValid = false;
            }

            if ($thisWeekActual->cpc != $cpcGame) {
                $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("CPC actual for this week ({$thisWeekActual->cpc}) does not match reported value ({$cpcGame}).");
                $isValid = false;
            }

            if ($thisWeekActual->t1x != $t1xGame) {
                // This is a warning since the regional is asked to verify
                $this->messages['warnings'][] = Message::create('Current Weekly Stats')->reportWarning("T1X actual approved for this week ({$thisWeekActual->t1x}) does not match number of T1 incoming with approval dates during this quarter ({$t1xGame}). If the sheet does not detect this, the quarter starting totals are likely inaccurate. Verify manually.");
            }

            if ($thisWeekActual->t2x != $t2xGame) {
                $this->messages['warnings'][] = Message::create('Current Weekly Stats')->reportWarning("T2X actual approved for this week ({$thisWeekActual->t2x}) does not match number of T2 incoming with approval dates during this quarter ({$t2xGame}). If the sheet does not detect this, the quarter starting totals are likely inaccurate. Verify manually.");
            }

            if ($thisWeekActual->gitw != $gitwGame) {
                $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("GITW actual for this week ({$thisWeekActual->gitw}%) does not match the total number of team members reported as effective ({$gitwGame}%).");
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function validateStatsReport()
    {
        $isValid = true;

        if ($this->enforceVersion && $this->statsReport->spreadsheetVersion != $this->center->sheetVersion) {

            $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Spreadsheet version ({$this->statsReport->spreadsheetVersion}) doesn't match expected version ({$this->center->sheetVersion}).");
            $isValid = false;
        }

        if ($this->expectedDate && $this->expectedDate->ne($this->statsReport->reportingDate)) {

            $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Spreadsheet date ({$this->statsReport->reportingDate->toDateString()}) doesn't match expected date ({$this->expectedDate->toDateString()}).");
            $isValid = false;
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
            $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Could not find center '$centerName'. The name may not match our list or this sheet may be an invalid/corrupt.");
        } else if (!$this->center->active) {
            $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Center '$centerName' is marked as inactive. Please have an administrator activate this center if they are now an active team.");
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
            $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Reporting date format was incorrect, '$reportingDate'. Please input date explicitly (i.e. {$this->reportingDate->format('M d, Y')}).");
        }

        if (!$this->reportingDate || $this->reportingDate->lt(Carbon::create(1980,1,1,0,0,0))) {
            $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Could not find reporting date. Got '$reportingDate'. This may be an invalid/corrupt sheet.");
        }
    }
    protected function loadVersion()
    {
        if ($this->version === null) {

            $data = $this->getWeeklyStatsSheet();

            $version = $data[2]['L'];

            if (!preg_match("/^V((\d+\.\d+)(\.\d+)?)$/i", $version, $matches)) {
                $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Version '$version' is in an incorrect format. Sheet may be invalid/corrupt.");
            } else {
                $this->version = $matches[1]; // only grab to num
            }
        }
    }
    protected function loadQuarter()
    {
        $this->quarter = Quarter::findByDate($this->reportingDate);
        if (!$this->quarter) {
            $this->messages['errors'][] = Message::create('Current Weekly Stats')->reportError("Could not find quarter with date '{$this->reportingDate->toDateString()}'. This may be an invalid/corrupt sheet");
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

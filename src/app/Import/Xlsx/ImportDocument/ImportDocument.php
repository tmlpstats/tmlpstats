<?php
namespace TmlpStats\Import\Xlsx\ImportDocument;

use TmlpStats\CenterStatsData;
use TmlpStats\GlobalReport;
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

        foreach ($this->importers as $type => $importer) {

            $list = $importer->getData();

            foreach ($list as $dataArray) {

                $data = Util::arrayToObject($dataArray);
                $validator = $this->getValidator($type);
                if (!$validator->run($data)) {
                    $isValid = false;
                }
                $this->mergeMessages($validator->getMessages());
            }
        }

        if (!$this->validateStatsReport()) {
            $isValid = false;
        }
        if (!$this->validateTeamExpansion()) {
            $isValid = false;
        }
        if (!$this->validateCenterGames()) {
            $isValid = false;
        }
        return $isValid;
    }

    protected function validateTeamExpansion()
    {
        $isValid = true;

        $qStartCurrentTeam1Registered = 0;
        $qStartFutureTeam1Registered  = 0;
        $qStartCurrentTeam1Approved   = 0;
        $qStartFutureTeam1Approved    = 0;

        $qStartCurrentTeam2Registered = 0;
        $qStartFutureTeam2Registered  = 0;
        $qStartCurrentTeam2Approved   = 0;
        $qStartFutureTeam2Approved    = 0;

        $tmlpRegistrations = $this->importers['tmlpRegistration']->getData();
        foreach ($tmlpRegistrations as $registration) {

            $regDate = Util::parseUnknownDateFormat($registration['regDate']);
            if ($registration['incomingWeekend'] == 'current') {

                if ($regDate && $regDate->lte($this->quarter->startWeekendDate)) {
                    if ($registration['incomingTeamYear'] == 1) {
                        $qStartCurrentTeam1Registered++;
                    } else {
                        $qStartCurrentTeam2Registered++;
                    }
                }
            } else {

                if ($regDate && $regDate->lte($this->quarter->startWeekendDate)) {
                    if ($registration['incomingTeamYear'] == 1) {
                        $qStartFutureTeam1Registered++;
                    } else {
                        $qStartFutureTeam2Registered++;
                    }
                }
            }

            if ($registration['apprDate']) {
                $apprDate = Util::parseUnknownDateFormat($registration['apprDate']);
                if ($registration['incomingWeekend'] == 'current') {

                    if ($apprDate && $apprDate->lte($this->quarter->startWeekendDate)) {
                        if ($registration['incomingTeamYear'] == 1) {
                            $qStartCurrentTeam1Approved++;
                        } else {
                            $qStartCurrentTeam2Approved++;
                        }
                    }
                } else {

                    if ($apprDate && $apprDate->lte($this->quarter->startWeekendDate)) {
                        if ($registration['incomingTeamYear'] == 1) {
                            $qStartFutureTeam1Approved++;
                        } else {
                            $qStartFutureTeam2Approved++;
                        }
                    }
                }
            }
        }

        $quarterStart = clone $this->quarter->startWeekendDate;
        $firstWeekDate = $quarterStart->addDays(7);

        $qStartTotalTeam1Registered = 0;
        $qStartTotalTeam1Approved   = 0;
        $qStartTotalTeam2Registered = 0;
        $qStartTotalTeam2Approved   = 0;

        $tmlpGames = $this->importers['tmlpCourseInfo']->getData();
        foreach ($tmlpGames as $game) {

            $calculatedRegistered = 0;
            $calculatedApproved   = 0;

            switch ($game['type']) {

                case 'Incoming T1':
                    $calculatedRegistered        = $qStartCurrentTeam1Registered;
                    $calculatedApproved          = $qStartCurrentTeam1Approved;

                    $qStartTotalTeam1Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam1Approved   += $game['quarterStartApproved'];
                    break;
                case 'Future T1':
                    $calculatedRegistered        = $qStartFutureTeam1Registered;
                    $calculatedApproved          = $qStartFutureTeam1Approved;

                    $qStartTotalTeam1Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam1Approved   += $game['quarterStartApproved'];
                    break;
                case 'Incoming T2':
                    $calculatedRegistered        = $qStartCurrentTeam2Registered;
                    $calculatedApproved          = $qStartCurrentTeam2Approved;

                    $qStartTotalTeam2Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam2Approved   += $game['quarterStartApproved'];
                    break;
                case 'Future T2':
                    $calculatedRegistered        = $qStartFutureTeam2Registered;
                    $calculatedApproved          = $qStartFutureTeam2Approved;

                    $qStartTotalTeam2Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam2Approved   += $game['quarterStartApproved'];
                    break;
                // default: ignore (only with corrupt sheet). An error is reported already
            }

            // Validate Quarter starting totals on the first week
            if ($this->reportingDate->eq($firstWeekDate)) {

                if ($game['quarterStartRegistered'] != $calculatedRegistered) {
                    $this->addMessage(static::TAB_COURSES, 'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $game['type'], $game['quarterStartRegistered'], $calculatedRegistered);
                    $isValid = false;
                }

                if ($game['quarterStartApproved'] != $calculatedApproved) {
                    $this->addMessage(static::TAB_COURSES, 'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $game['type'], $game['quarterStartApproved'], $calculatedApproved);
                    $isValid = false;
                }
            }
        }

        // Validate Quarter starting totals on mid quarter weeks. (Registrations may move between current and future)
        if ($this->reportingDate->ne($firstWeekDate)) {

            $calculatedTotals = $qStartCurrentTeam1Registered + $qStartFutureTeam1Registered;
            if ($qStartTotalTeam1Registered != $calculatedTotals) {
                $this->addMessage(static::TAB_COURSES, 'IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $qStartTotalTeam1Registered, $calculatedTotals);
            }

            $calculatedTotals = $qStartCurrentTeam1Approved + $qStartFutureTeam1Approved;
            if ($qStartTotalTeam1Approved != $calculatedTotals) {
                $this->addMessage(static::TAB_COURSES, 'IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $qStartTotalTeam1Approved, $calculatedTotals);
            }

            $calculatedTotals = $qStartCurrentTeam2Registered + $qStartFutureTeam2Registered;
            if ($qStartTotalTeam2Registered != $calculatedTotals) {
                $this->addMessage(static::TAB_COURSES, 'IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $qStartTotalTeam2Registered, $calculatedTotals);
            }

            $calculatedTotals = $qStartCurrentTeam2Approved + $qStartFutureTeam2Approved;
            if ($qStartTotalTeam2Approved != $calculatedTotals) {
                $this->addMessage(static::TAB_COURSES, 'IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $qStartTotalTeam2Approved, $calculatedTotals);
            }
        }

        return $isValid;
    }

    protected function validateCenterGames()
    {
        $isValid = true;
        $thisWeekActual = null;

        $centerStats = $this->importers['centerStats']->getData();

        foreach ($centerStats as $week) {

            if ($week['type'] == 'actual'
                && $week['reportingDate'] == $this->reportingDate->toDateString()) {

                $thisWeekActual = $week;
                break;
            }
        }

        // GITW and TDO
        $classList = $this->importers['classList']->getData();
        $activeMemberCount = 0;
        $effectiveCount    = 0;
        $tdoCount          = 0;

        foreach ($classList as $member) {

            if ($member['wd'] || $member['wbo'] || $member['xferOut']) {
                continue;
            }

            $activeMemberCount++;
            if (preg_match('/^E$/i', $member['gitw'])) {
                $effectiveCount++;
            }
        }

        $gitwGame = $activeMemberCount ? round(($effectiveCount/$activeMemberCount) * 100) : 0;

        // CAP & CPC Game
        $courses = $this->importers['commCourseInfo']->getData();
        $capCurrentStandardStarts = 0;
        $capQStartStandardStarts  = 0;
        $cpcCurrentStandardStarts = 0;
        $cpcQStartStandardStarts  = 0;

        foreach ($courses as $course) {
            if ($course['type'] == 'CAP') {
                $capCurrentStandardStarts += $course['currentStandardStarts'];
                $capQStartStandardStarts  += $course['quarterStartStandardStarts'];
            } else if ($course['type'] == 'CPC') {
                $cpcCurrentStandardStarts += $course['currentStandardStarts'];
                $cpcQStartStandardStarts  += $course['quarterStartStandardStarts'];
            }
        }

        $capGame = $capCurrentStandardStarts - $capQStartStandardStarts;
        $cpcGame = $cpcCurrentStandardStarts - $cpcQStartStandardStarts;

        // T1x and T2x Games
        $registrations = $this->importers['tmlpRegistration']->getData();
        $t1CurrentApproved = 0;
        $t2CurrentApproved  = 0;
        foreach ($registrations as $registration) {
            if ($registration['appr'] && $registration['incomingTeamYear'] == 1) {
                $t1CurrentApproved++;
            } else if ($registration['appr']) {
                $t2CurrentApproved++;
            }
        }

        $tmlpGames = $this->importers['tmlpCourseInfo']->getData();
        $t1QStartApproved = 0;
        $t2QStartApproved  = 0;
        foreach ($tmlpGames as $game) {
            if (preg_match('/T1/', $game['type'])) {
                $t1QStartApproved += $game['quarterStartApproved'];
            } else if (preg_match('/T2/', $game['type'])) {
                $t2QStartApproved += $game['quarterStartApproved'];
            }
        }

        $t1xGame = $t1CurrentApproved - $t1QStartApproved;
        $t2xGame = $t2CurrentApproved - $t2QStartApproved;

        // Make sure they match
        if ($thisWeekActual) {

            if ($thisWeekActual['cap'] != $capGame) {
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_CAP_ACTUAL_INCORRECT', $thisWeekActual['cap'], $capGame);
                $isValid = false;
            }

            if ($thisWeekActual['cpc'] != $cpcGame) {
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_CPC_ACTUAL_INCORRECT', $thisWeekActual['cpc'], $cpcGame);
                $isValid = false;
            }

            if ($thisWeekActual['t1x'] != $t1xGame) {
                // This is a warning since the regional is asked to verify
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_T1X_ACTUAL_INCORRECT', $thisWeekActual['t1x'], $t1xGame);
            }

            if ($thisWeekActual['t2x'] != $t2xGame) {
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_T2X_ACTUAL_INCORRECT', $thisWeekActual['t2x'], $t2xGame);
            }

            if ($thisWeekActual['gitw'] != $gitwGame) {
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_GITW_ACTUAL_INCORRECT', $thisWeekActual['gitw'], $gitwGame);
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function validateStatsReport()
    {
        $isValid = true;

        if ($this->enforceVersion && $this->version != $this->center->sheetVersion) {

            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_SPREADSHEET_VERSION_MISMATCH', $this->version, $this->center->sheetVersion);
            $isValid = false;
        }

        if ($this->expectedDate && $this->expectedDate->ne($this->statsReport->reportingDate)) {

            if ($this->statsReport->reportingDate->diffInDays($this->statsReport->quarter->endWeekendDate) < 7) {
                // Reporting in the last week of quarter
                if ($this->statsReport->reportingDate->ne($this->statsReport->quarter->endWeekendDate)) {
                    $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_SPREADSHEET_DATE_MISMATCH_LAST_WEEK', $this->statsReport->reportingDate->toDateString(), $this->statsReport->quarter->endWeekendDate->toDateString());
                    $isValid = false;
                }
            } else {
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_SPREADSHEET_DATE_MISMATCH', $this->statsReport->reportingDate->toDateString(), $this->expectedDate->toDateString());
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

        $this->statsReport = StatsReport::firstOrCreate(array(
            'center_id'           => $this->center->id,
            'quarter_id'          => $this->quarter->id,
            'reporting_date'      => $this->reportingDate->toDateString(),
            'submitted_at'        => null,
        ));
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
            $centerListObjects = Center::globalRegion(Auth::user()->homeRegion())->orderBy('name')->get();
            $centerList = array();

            foreach ($centerListObjects as $center) {
                $centerList[] = $center->name;
            }

            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_CENTER_NOT_FOUND', $centerName, implode(', ', $centerList));
        } else if (!$this->center->active) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_CENTER_INACTIVE', $centerName);
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
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_DATE_FORMAT_INCORRECT', $reportingDate);
        }

        if (!$this->reportingDate || $this->reportingDate->lt(Carbon::create(1980,1,1,0,0,0))) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_DATE_NOT_FOUND', $reportingDate);
        }
    }
    protected function loadVersion()
    {
        if ($this->version === null) {

            $data = $this->getWeeklyStatsSheet();

            $version = $data[2]['L'];

            if (!preg_match("/^V((\d+\.\d+)(\.\d+)?)$/i", $version, $matches)) {
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_VERSION_FORMAT_INCORRECT', $version);
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
        $this->quarter = Quarter::byRegion($this->center->region)
            ->date($this->reportingDate)
            ->first();
        if (!$this->quarter) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_QUARTER_NOT_FOUND', $this->reportingDate->toDateString());
        } else {
            // TODO: figure out how to not have to do this
            $this->quarter->setRegion($this->center->region);
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

        $this->importers['tmlpCourseInfo'] = $importer;
    }

    protected function addMessage($tab, $messageId)
    {
        $message = Message::create($tab);

        $arguments = array_slice(func_get_args(), 2);
        array_unshift($arguments, $messageId, false);

        $message = $this->callMessageAdd($message, $arguments);

        $this->mergeMessages(array($message));
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
        // Don't save anything if report is locked
        if ($this->statsReport->locked) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_STATS_REPORT_LOCKED', $this->center->name, $this->reportingDate->format("M d, Y"));
            return false;
        }

        if ($this->statsReport->validated) {
            foreach ($this->importers as $name => $importer) {

                $importer->postProcess();

                // Update the Stats Report after post processing
                switch ($name) {
                    case 'contactInfo':
                        $reportingStatistician = $importer->getReportingStatistician();

                        $actual = CenterStatsData::actual()
                            ->byStatsReport($this->statsReport)
                            ->reportingDate($this->statsReport->reportingDate)
                            ->first();
                        if ($actual) {
                            $actual->programManagerAttendingWeekend = $importer->getProgramManagerAttendingWeekend();
                            $actual->classroomLeaderAttendingWeekend = $importer->getClassroomLeaderAttendingWeekend();
                            $actual->save();
                        }

                        $this->statsReport->reportingStatisticianId = $reportingStatistician ? $reportingStatistician->id : null;
                        break;

                    default:
                        break;
                }
            }
        }

        $this->statsReport->version = $this->version;
        $this->statsReport->submittedAt = $this->submittedAt ?: Carbon::now();
        $this->statsReport->save();

        $this->globalReport = GlobalReport::firstOrCreate([
            'reporting_date' => $this->reportingDate,
        ]);
        $this->globalReport->addCenterReport($this->statsReport);

        $this->saved = true;

        return true;
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

    // @codeCoverageIgnoreStart
    protected function callMessageAdd($message, $arguments)
    {
        return call_user_func_array(array($message, 'addMessage'), $arguments);
    }
    // @codeCoverageIgnoreEnd
}

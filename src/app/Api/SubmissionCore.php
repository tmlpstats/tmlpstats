<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;
use Mail;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Exceptions;
use TmlpStats\Domain;

class SubmissionCore extends AuthenticatedApiBase
{
    /**
     * Initialize a submission, checking if parameters are valid.
     * @param  Models\Center $center
     * @param  Carbon        $reportingDate
     * @return array
     */
    public function initSubmission(Models\Center $center, Carbon $reportingDate)
    {
        $this->checkCenterDate($center, $reportingDate);

        // Make sure a global report exists
        $globalReport = Models\GlobalReport::firstOrCreate([
            'reporting_date' => $reportingDate,
        ]);

        $localReport = App::make(LocalReport::class);
        $rq = $this->reportAndQuarter($center, $reportingDate);

        $lastValidReport = $rq['report'];
        $quarter = $rq['quarter'];
        $centerQuarter = Domain\CenterQuarter::ensure($center, $quarter);

        if ($lastValidReport === null) {
            $team_members = [];
        } else {
            $team_members = $localReport->getClassList($lastValidReport);
        }

        // Get values for lookups
        $withdraw_codes = Models\WithdrawCode::get();
        $validRegQuarters = App::make(Api\Application::class)->validRegistrationQuarters($center, $reportingDate, $quarter);
        $validStartQuarters = App::make(Api\TeamMember::class)->validStartQuarters($center, $reportingDate, $quarter);
        $accountabilities = Models\Accountability::orderBy('name')->get();
        $centers = Models\Center::byRegion($center->getGlobalRegion())->active()->orderBy('name')->get();

        return [
            'success' => true,
            'id' => $center->id,
            'validRegQuarters' => $validRegQuarters,
            'validStartQuarters' => $validStartQuarters,
            'lookups' => compact('withdraw_codes', 'team_members', 'center', 'centers'),
            'accountabilities' => $accountabilities,
            'currentQuarter' => $centerQuarter,
        ];
    }

    /**
     * Finalizes a submission
     *
     * @param  Models\Center $center
     * @param  Carbon        $reportingDate
     * @return array
     */
    public function completeSubmission(Models\Center $center, Carbon $reportingDate)
    {
        $this->checkCenterDate($center, $reportingDate);

        $this->assertAuthz($this->context->can('submitStats', $center));

        $results = App::make(ValidationData::class)->validate($center, $reportingDate);
        if (!$results['valid']) {
            // TODO: figure out what we want to do here
            // validation failed. for now, exit
            return [
                'success' => false,
                'id' => $center->id,
                'message' => 'Validation failed. Please correct issues indicated on the Review page and try again.',
            ];
        }

        $reportingDate->startOfDay();

        DB::beginTransaction();
        $debug_message = '';
        $person_id = -1;
        $reg_id = -1;

        try {
            // Create stats_report record and get id
            $statsReport = LocalReport::ensureStatsReport($center, $reportingDate);
            $statsReport->validated = true;
            $statsReport->locked = true;
            $statsReport->submittedAt = Carbon::now();
            $statsReport->userId = $this->context->getUser()->id;
            $statsReport->save();

            $lastStatsReportDate = $reportingDate->copy()->subWeek();

            $reportNow = $reportingDate->copy()->setTime(15, 0, 0);
            $quarterEndDate = $statsReport->quarter->getQuarterEndDate($statsReport->center)->setTime(14, 59, 59);

            $isFirstWeek = $statsReport->reportingDate->eq($statsReport->quarter->getFirstWeekDate($statsReport->center));

            $debug_message .= ' sr_id=' . $statsReport->id;

            // Insert Actuals
            DB::insert('insert into center_stats_data
                        (id, reporting_date, type, tdo, cap, cpc, t1x, t2x, gitw, lf, points,
                            program_manager_attending_weekend, classroom_leader_attending_weekend,
                            stats_report_id, created_at, updated_at)
                        select null, stored_id, type, tdo, cap, cpc, t1x, t2x, gitw, lf, points,
                            null, null, ?, sysdate(), sysdate()
                        from submission_data_scoreboard
                        where center_id = ? and reporting_date = ? and stored_id = ?',
                [$statsReport->id, $center->id, $reportingDate->toDateString(), $reportingDate->toDateTimeString()]);
            $cs_id = DB::getPdo()->lastInsertId();
            $debug_message .= ' csa_id=' . $cs_id;

            // Insert Promises
            DB::insert('insert into center_stats_data
                        (id, reporting_date, type, tdo, cap, cpc, t1x, t2x, gitw, lf,
                            program_manager_attending_weekend, classroom_leader_attending_weekend,
                            stats_report_id, created_at, updated_at)
                        select null, promise_date, type, 100,  cap, cpc, t1x, t2x, gitw, lf,
                            null, null, ?, sysdate(), sysdate()
                        from submission_data_promises
                        where center_id = ? and reporting_date = ?',
                [$statsReport->id, $center->id, $reportingDate->toDateString()]);
            $csp_id = DB::getPdo()->lastInsertId();
            $debug_message .= ' csp_id=' . $csp_id;

            // Process applications
            // Loop through all applications in submission data and do the following:
            // - if application is new (stored_id<0), then insert new person, otherwise update info in people table
            // - insert new records into tmlp_registration_data
            //
            // ? - Possibly run the "carry over" for all ones that were not changed
            //      by
            $result = DB::select('select i.* from submission_data_applications i
                                    left outer join tmlp_registrations r
                                        on r.id=i.stored_id
                                    where i.center_id=?  and i.reporting_date=?;',
                [$center->id, $reportingDate->toDateString()]);
            if (!empty($result)) {
                foreach ($result as $r) {
                    if ($r->stored_id < 0) {
                        // This is a new application, create the things
                        DB::insert('insert into people
                                        (first_name, last_name, email, center_id, created_at, updated_at)
                                            select i.first_name, i.last_name, i.email, i.center_id, sysdate(), sysdate()
                                        from submission_data_applications i where i.id=?',
                            [$r->id]);
                        $person_id = DB::getPdo()->lastInsertId();
                        $debug_message .= ' sreg_id=' . $r->id . ' person_id=' . $person_id;

                        DB::insert('insert into tmlp_registrations
                                        (person_id, team_year, reg_date, is_reviewer, created_at, updated_at)
                                        select ?, team_year, regDate, isReviewer, sysdate(), sysdate()
                                        from submission_data_applications i where i.id=?',
                            [$person_id, $r->id]);
                        $reg_id = DB::getPdo()->lastInsertId();
                        $debug_message .= ' reg_id=' . $reg_id;

                        // Update submission_data with new id so we don't overwrite if the report is resubmitted
                        DB::update('update submission_data set stored_id=?, data = JSON_SET(data, "$.id", ?) where id=?', [$reg_id, $reg_id, $r->id]);
                    } else {
                        // This is an existing application, update the things
                        DB::update('update people p, submission_data_applications sda
                                    set p.updated_at=sysdate(),
                                        p.first_name=sda.first_name,
                                        p.last_name=sda.last_name,
                                        p.email=sda.email,
                                        p.updated_at=sysdate()
                                    where p.id=sda.person_id
                                          and sda.id=?
                                          and (coalesce(p.first_name,\'\') != coalesce(sda.first_name,\'\')
                                                or coalesce(p.last_name,\'\') != coalesce(sda.last_name,\'\')
                                                or coalesce(p.email,\'\') != coalesce(sda.email,\'\')
                                          )',
                            [$r->id]);
                        DB::update('update tmlp_registrations p, submission_data_applications sda
                                    set p.updated_at=sysdate(),
                                        p.team_year=sda.team_year,
                                        p.reg_date=sda.regDate,
                                        p.is_reviewer=sda.isReviewer,
                                        p.updated_at=sysdate()
                                    where p.id=sda.stored_id
                                          and sda.id=?
                                          and (coalesce(p.team_year,\'\') != coalesce(sda.team_year,\'\')
                                                or coalesce(p.reg_date,\'\') != coalesce(sda.regDate,\'\')
                                                or coalesce(p.is_reviewer,\'\') != coalesce(sda.isReviewer,\'\'))',
                            [$r->id]);
                        $reg_id = $r->stored_id;
                        $person_id = $r->person_id;
                    };

                    // Create application data row
                    DB::insert('insert into tmlp_registrations_data
                                (tmlp_registration_id, reg_date, app_out_date, app_in_date, appr_date, wd_date,
                                    withdraw_code_id, committed_team_member_id, incoming_quarter_id, comment, travel, room, stats_report_id, created_at, updated_at)
                                select ?, regDate,appOutDate,appinDate,apprDate,wdDate, withdrawCode,committeddteamMember,
                                incomingQuarter,comment,travel,room,?, sysdate(),sysdate()
                                from submission_data_applications i where i.id=?;',
                        [$reg_id, $statsReport->id, $r->id]);

                    $trd_id = DB::getPdo()->lastInsertId();
                    $debug_message .= ' trd_id=' . $trd_id;
                }
            }// end application processing

            // Insert data rows for any applications that weren't updated this week
            if (!$isFirstWeek) {
                $affected = DB::insert('INSERT INTO tmlp_registrations_data
                        (tmlp_registration_id, reg_date, app_out_date, app_in_date, appr_date,
                        wd_date, withdraw_code_id, committed_team_member_id, incoming_quarter_id,
                        comment, travel, room, stats_report_id, created_at, updated_at)
                    SELECT  trd.tmlp_registration_id, trd.reg_date, trd.app_out_date, trd.app_in_date,
                            trd.appr_date, trd.wd_date, trd.withdraw_code_id, trd.committed_team_member_id,
                            trd.incoming_quarter_id, trd.comment, trd.travel, trd.room, ?, sysdate(), sysdate()
                    FROM tmlp_registrations_data trd
                    INNER JOIN stats_reports sr ON sr.id = trd.stats_report_id
                    INNER JOIN global_report_stats_report grsr ON grsr.stats_report_id = trd.stats_report_id
                    WHERE
                        sr.center_id = ?
                        AND sr.reporting_date = ?
                        AND trd.tmlp_registration_id NOT IN (SELECT tmlp_registration_id FROM tmlp_registrations_data WHERE stats_report_id = ?)',
                    [$statsReport->id, $center->id, $lastStatsReportDate->toDateString(), $statsReport->id]);
                $debug_message .= ' last-rep=' . $lastStatsReportDate->toDateString() . ' ins-tmd=' . $affected;
            }

            $result = DB::select('select i.* from submission_data_team_members i
                                    left outer join team_members t
                                        on t.id=i.team_member_id
                                    where i.center_id=?  and i.reporting_date=?;',
                [$center->id, $reportingDate->toDateString()]);
            if (!empty($result)) {
                foreach ($result as $r) {
                    $tm_id = -1;
                    if ($r->team_member_id < 0) {
                        // This is a new team member, create the things
                        DB::insert('insert into people
                                        (first_name, last_name, email, phone, center_id, created_at, updated_at)
                                    select i.first_name, i.last_name, i.email, i.phone, i.center_id, sysdate(), sysdate()
                                    from submission_data_team_members i where i.id=?',
                            [$r->id]);
                        $person_id = DB::getPdo()->lastInsertId();
                        $debug_message .= ' snewtm_id=' . $r->id . ' person_id=' . $person_id;

                        DB::insert('insert into team_members
                                        (person_id, team_year, incoming_quarter_id, is_reviewer, created_at, updated_at)
                                    select ?, team_year, incoming_quarter_id, is_reviewer, sysdate(), sysdate()
                                    from submission_data_team_members i where i.id=?',
                            [$person_id, $r->id]);
                        $tm_id = DB::getPdo()->lastInsertId();
                        $debug_message .= ' newtm_id=' . $tm_id;

                        // Update submission_data with new id so we don't overwrite if the report is resubmitted
                        DB::update('update submission_data set stored_id=?, data = JSON_SET(data, "$.id", ?) where id=?', [$tm_id, $tm_id, $r->id]);
                    } else {
                        // This is an existing application, update the things
                        DB::update('update people p, submission_data_team_members sda
                                    set p.updated_at=sysdate(),
                                        p.first_name=sda.first_name,
                                        p.last_name=sda.last_name,
                                        p.email=sda.email,
                                        p.phone=sda.phone,
                                        p.updated_at=sysdate()
                                    where p.id=sda.person_id
                                          and sda.id=?
                                          and (coalesce(p.first_name,\'\') != coalesce(sda.first_name,\'\')
                                                or coalesce(p.last_name,\'\') != coalesce(sda.last_name,\'\')
                                                or coalesce(p.email,\'\') != coalesce(sda.email,\'\')
                                                or coalesce(p.phone,\'\') != coalesce(sda.phone,\'\')
                                          )',
                            [$r->id]);
                        DB::update('update team_members p, submission_data_team_members sda
                                    set p.updated_at=sysdate(),
                                        p.team_year=sda.team_year,
                                        p.incoming_quarter_id=sda.incoming_quarter_id,
                                        p.is_reviewer=sda.is_reviewer,
                                        p.updated_at=sysdate()
                                    where p.id=sda.team_member_id
                                          and sda.id=?
                                          and (coalesce(p.team_year,\'\') != coalesce(sda.team_year,\'\')
                                                or coalesce(p.incoming_quarter_id,\'\') != coalesce(sda.incoming_quarter_id,\'\')
                                                or coalesce(p.is_reviewer,\'\') != coalesce(sda.is_reviewer,\'\'))',
                            [$r->id]);
                        $tm_id = $r->team_member_id;
                        $person_id = $r->person_id;
                    };

                    // Create team member data row
                    DB::insert('insert into team_members_data
                                    (team_member_id, at_weekend, xfer_out, xfer_in, ctw, withdraw_code_id, travel, room, comment,
                                    gitw, tdo, stats_report_id, created_at, updated_at)
                                select ?, atWeekend, xfer_in, xfer_out, ctw, withdrawCode, travel, room, comment,
                                    gitw, tdo, ?, sysdate(), sysdate()
                                from submission_data_team_members
                                where center_id=? and reporting_date=? and team_member_id=?',
                            [$tm_id, $statsReport->id, $center->id, $reportingDate->toDateString(), $tm_id]);
                    $tmd_id = DB::getPdo()->lastInsertId();
                    $debug_message .= ' last_tmd_id=' . $tmd_id;
                }
            }// end team member processing

            // Insert data rows for any team members that have withdrawn and weren't updated this week
            if (!$isFirstWeek) {
                DB::insert('INSERT INTO team_members_data
                        (team_member_id, at_weekend, xfer_out, xfer_in, ctw, withdraw_code_id,
                        travel, room, comment, gitw, tdo, stats_report_id, created_at, updated_at)
                    SELECT  tmd.team_member_id, tmd.at_weekend, tmd.xfer_out, tmd.xfer_in, tmd.ctw,
                            tmd.withdraw_code_id, tmd.travel, tmd.room, tmd.comment, tmd.gitw, tmd.tdo,
                            ?, sysdate(), sysdate()
                    FROM team_members_data tmd
                    INNER JOIN stats_reports sr ON sr.id = tmd.stats_report_id
                    INNER JOIN global_report_stats_report grsr ON grsr.stats_report_id = tmd.stats_report_id
                    WHERE
                        sr.center_id = ?
                        AND sr.reporting_date = ?
                        AND tmd.withdraw_code_id IS NOT NULL
                        AND tmd.team_member_id NOT IN (SELECT team_member_id FROM team_members_data WHERE stats_report_id = ?)',
                    [$statsReport->id, $center->id, $lastStatsReportDate->toDateString(), $statsReport->id]);
            }

            //Insert course data
            $result = DB::select('select i.* from submission_data_courses i
                                       where i.center_id=?  and i.reporting_date=? and i.course_id<0',
                [$center->id, $reportingDate->toDateString()]);
            foreach ($result as $r) {
                    DB::insert('insert into  courses
                                    ( id, start_date, type, location, center_id,  created_at, updated_at)
                                        select null, i.start_date, i.type, i.location, i.center_id,  sysdate(), sysdate()
                                    from submission_data_courses i where i.id=?',
                        [$r->id]);
                    $new_course_id = DB::getPdo()->lastInsertId();
                    $debug_message .= ' new_course_id=' . $new_course_id;

                    // Update submission_data with new id so we don't overwrite if the report is resubmitted
                    DB::update('update submission_data set stored_id=?, data = JSON_SET(data, "$.id", ?) where id=?', [$new_course_id, $new_course_id, $r->id]);
            }
            $affected = DB::insert(
                'insert into courses_data
                    (id, course_id, quarter_start_ter, quarter_start_standard_starts, quarter_start_xfer, current_ter, current_standard_starts, current_xfer, completed_standard_starts, potentials, registrations, guests_promised, guests_invited, guests_confirmed, guests_attended, stats_report_id, created_at, updated_at)
                 select null, course_id, quarter_start_ter, quarter_start_standard_starts,  quarter_start_xfer, current_ter, current_standard_starts, current_xfer, completed_standard_starts, potentials, registrations, guests_promised, guests_invited, guests_confirmed, guests_attended, ?, sysdate(),sysdate()
                 from submission_data_courses where center_id=? and reporting_date=?',
                [$statsReport->id, $center->id, $reportingDate->toDateString()]);
            $debug_message .= ' upd_courses_rows=' . $affected;

            if (!$isFirstWeek) {
                $affected = DB::insert(
                    'insert into courses_data
                        (id, course_id, quarter_start_ter, quarter_start_standard_starts, quarter_start_xfer, current_ter, current_standard_starts, current_xfer, completed_standard_starts, potentials, registrations, guests_promised, guests_invited, guests_confirmed, guests_attended, stats_report_id, created_at, updated_at)
                     select null, course_id, quarter_start_ter, quarter_start_standard_starts,  quarter_start_xfer, current_ter, current_standard_starts, current_xfer, completed_standard_starts, potentials, registrations, guests_promised, guests_invited, guests_confirmed, guests_attended, ?, sysdate(),sysdate()
                     from courses_data
                            where stats_report_id in
                                (select id from global_report_stats_report gr, stats_reports s
                                        where gr.stats_report_id=s.id
                                            and s.reporting_date=? and
                                            s.center_id=?
                                )
                                and course_id not in
                                    (select course_id from submission_data_courses
                                        where center_id=? and reporting_date=?)',
                    [$statsReport->id,$lastStatsReportDate->toDateString(),$center->id,
                      $center->id,$reportingDate->toDateString()]);
            }

            // Add/update all accountability holders
            $result = collect(DB::select(
                'select i.* from submission_data_accountabilities i
                    where i.center_id=? and i.reporting_date=?',
                [$center->id, $reportingDate->toDateString()]
            ))->keyBy(function ($item) {
                return $item->accountability_id;
            });

            // Skip program managers and classroom leaders for now
            // TODO: we'll need to import them at some point
            $allAccountabilities = Models\Accountability::context('team')->whereNotIn('id', [8, 9])->get();
            foreach ($allAccountabilities as $accountability) {
                if (!isset($result[$accountability->id])) {
                    // No one is listed as accountable, remove any existing accountables
                    DB::update("
                        UPDATE  accountability_person ap
                        INNER JOIN people p ON p.id = ap.person_id
                        SET ap.ends_at = ?, ap.updated_at = sysdate()
                        WHERE
                            ap.accountability_id = ?
                            AND p.center_id = ?
                            AND (ap.ends_at IS NULL OR ap.ends_at > ?)",
                        [$reportNow->copy()->subSecond(), $accountability->id, $center->id, $reportNow]
                    );
                    continue;
                }

                $person = Models\Person::find($result[$accountability->id]->person_id);
                if (!$person) {
                    Log::error("Person {$result[$accountability->id]->person_id} was submitted in accountability_person but doesn't exist.");
                    continue;
                }

                // If the person doesn't already have this accountability, add it and remove previous holder
                if (!$person->hasAccountability($accountability, $reportNow)) {
                    $person->takeoverAccountability($accountability, $reportNow, $quarterEndDate);
                }
            }

            // Mark stats report as 'official'
            $globalReport = Models\GlobalReport::firstOrCreate([
                'reporting_date' => $reportingDate,
            ]);
            $globalReport->addCenterReport($statsReport);
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'id' => $center->id,
                'message' => $e->getMessage(),
                'debug_message' => $debug_message,
            ];
        }

        $success = true;
        DB::commit();

        $emailResults = $this->sendStatsSubmittedEmail($statsReport);
        $submittedAt = $statsReport->submittedAt->copy()->setTimezone($center->timezone);

        return [
            'success' => $success,
            'id' => $center->id,
            'submittedAt' => $submittedAt->toDateTimeString(),
            'message' => $emailResults,
            'debug_message' => $debug_message,
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

    /**
     * Do the very common lookup of getting the last stats report and the quarter for a given
     * center-reportingdate pair.
     *
     * In the case there is no official report on dates before the given reportingDate,
     * (this happens on the first weekly submission) the report will be null.
     *
     * @param  Models\Center $center        The center we're getting the statsReport from
     * @param  Carbon        $reportingDate The reporting date of a stats report.
     * @return array[report, quarter]       An associative array with keys report and quarter
     */
    public function reportAndQuarter(Models\Center $center, Carbon $reportingDate)
    {
        $report = App::make(LocalReport::class)->getLastStatsReportSince($center, $reportingDate, ['official']);
        if ($report === null) {
            $quarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);
        } else {
            $quarter = $report->quarter;
        }

        return compact('report', 'quarter');
    }

    public function sendStatsSubmittedEmail(Models\StatsReport $statsReport)
    {
        $result = [];

        $user    = ucfirst($this->context->getUser()->firstName);
        $quarter = $statsReport->quarter;
        $center  = $statsReport->center;
        $region  = $center->region;
        $reportingDate = $statsReport->reportingDate;

        $submittedAt = $statsReport->submittedAt->copy()->setTimezone($center->timezone);

        $due               = $statsReport->due();
        $respondByDateTime = $statsReport->responseDue();

        $isLate = $submittedAt->gt($due);

        $reportNow = $reportingDate->copy()->setTime(15, 0, 0);

        $programManager         = $center->getProgramManager($reportNow);
        $classroomLeader        = $center->getClassroomLeader($reportNow);
        $t1TeamLeader           = $center->getT1TeamLeader($reportNow);
        $t2TeamLeader           = $center->getT2TeamLeader($reportNow);
        $statistician           = $center->getStatistician($reportNow);
        $statisticianApprentice = $center->getStatisticianApprentice($reportNow);

        $emailMap = [
            'center'                 => $center->statsEmail,
            'regional'               => $region->email,
            'programManager'         => $this->getEmail($programManager),
            'classroomLeader'        => $this->getEmail($classroomLeader),
            't1TeamLeader'           => $this->getEmail($t1TeamLeader),
            't2TeamLeader'           => $this->getEmail($t2TeamLeader),
            'statistician'           => $this->getEmail($statistician),
            'statisticianApprentice' => $this->getEmail($statisticianApprentice),
        ];

        $emailTo = $emailMap['center'] ?: $emailMap['statistician'];

        $mailingList = $center->getMailingList($quarter);

        if ($mailingList) {
            $emailMap['mailingList'] = $mailingList;
        }

        $emails = [];
        foreach ($emailMap as $accountability => $email) {

            if (!$email || $email == $emailTo) {
                continue;
            }

            if (is_array($email)) {
                $emails = array_merge($emails, $email);
            } else {
                $emails[] = $email;
            }
        }
        $emails = array_unique($emails);
        natcasesort($emails);

        $globalReport = Models\GlobalReport::reportingDate($statsReport->reportingDate)->first();

        $reportToken = Models\ReportToken::get($globalReport, $center);
        $reportUrl   = url("/report/{$reportToken->token}");

        $mobileDashUrl = "https://tmlpstats.com/m/" . strtolower($center->abbreviation);

        $submittedCount = Models\StatsReport::byCenter($center)
                                     ->reportingDate($statsReport->reportingDate)
                                     ->submitted()
                                     ->count();
        $isResubmitted = ($submittedCount > 1);

        $centerName = $center->name;
        $comment = $statsReport->submitComment;
        try {
            Mail::send('emails.apistatssubmitted',
                compact('centerName', 'comment', 'due', 'isLate', 'isResubmitted', 'mobileDashUrl',
                    'reportingDate', 'reportUrl', 'respondByDateTime', 'submittedAt', 'user'),
                function ($message) use ($emailTo, $emails, $emailMap, $centerName) {
                    // Only send email to centers in production
                    if (env('APP_ENV') === 'prod') {
                        $message->to($emailTo);
                        foreach ($emails as $email) {
                            $message->cc($email);
                        }
                    } else {
                        $message->to(env('ADMIN_EMAIL'));
                    }

                    if ($emailMap['regional']) {
                        $message->replyTo($emailMap['regional']);
                    }

                    $message->subject("Team {$centerName} Statistics Submitted");
                }
            );
            $successMessage = "<strong>Thank you.</strong> We received your statistics and notified the following emails"
                . " <ul><li>{$emailTo}</li><li>" . implode('</li><li>', $emails) . "</li></ul>"
                . " Please reply-all to that email if there is anything you need to communicate.";

            if (env('APP_ENV') === 'prod') {
                Log::info("Sent emails to the following people with team {$centerName}'s report: " . implode(', ', $emails));
            } else {
                Log::info("Sent emails to the following people with team {$centerName}'s report: " . env('ADMIN_EMAIL'));
                $successMessage .= "<br/><br/><strong>Since this is development, we sent it to "
                    . env('ADMIN_EMAIL') . " instead.</strong>";
            }

            return $successMessage;
        } catch (\Exception $e) {
            Log::error("Exception caught sending error email: " . $e->getMessage());

            return  "There was a problem emailing everyone about your stats. Please contact your"
                . " Regional Statistician ({$emailMap['regional']}) using your center stats email"
                . " ({$emailMap['center']}) letting them know.";
        }
    }

    public function getEmail(Models\Person $person = null)
    {
        if (!$person || $person->unsubscribed) {
            return null;
        }

        return $person->email;
    }
}

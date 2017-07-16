<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Log;
use Mail;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Exceptions;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;
use TmlpStats\Http\Controllers;

class SubmissionCore extends AuthenticatedApiBase
{
    /**
     * Initialize a submission UI, checking if parameters are valid and returning useful lookups.
     * @param  Models\Center $center
     * @param  Carbon        $reportingDate
     * @return array
     */
    public function initSubmission(Models\Center $center, Carbon $reportingDate)
    {
        $this->checkCenterDate($center, $reportingDate);

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

        $canSkipSubmitEmail = $this->context->can('skipSubmitEmail', $center);

        return [
            'success' => true,
            'id' => $center->id,
            'user' => compact('canSkipSubmitEmail'),
            'validRegQuarters' => $validRegQuarters,
            'validStartQuarters' => $validStartQuarters,
            'lookups' => compact('withdraw_codes', 'team_members', 'center', 'centers'),
            'accountabilities' => $accountabilities,
            'currentQuarter' => $centerQuarter,
            'systemMessages' => Models\SystemMessage::centerActiveMessages('submission', $center)->get(),
        ];
    }

    /**
     * Finalizes a submission
     *
     * @param  Models\Center $center
     * @param  Carbon        $reportingDate
     * @return array
     */
    public function completeSubmission(Models\Center $center, Carbon $reportingDate, array $data)
    {
        $this->checkCenterDate($center, $reportingDate, ['write']);

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

        $programLeaderApi = App::make(Api\ProgramLeader::class);

        try {
            // Create stats_report record and get id
            $statsReport = LocalReport::ensureStatsReport($center, $reportingDate);
            $statsReport->validated = true;
            $statsReport->locked = true;
            $statsReport->submittedAt = Carbon::now();
            $statsReport->validationMessages = $results['messages'];
            $statsReport->userId = $this->context->getUser()->id;
            $statsReport->submitComment = array_get($data, 'comment', null);
            $statsReport->save();

            $lastStatsReportDate = $reportingDate->copy()->subWeek();

            // Report is as of 3PM on Friday (technically this should be center time)
            $reportNow = $reportingDate->copy()->setTime(15, 0, 0);
            // Quarter is over (for accountables) at 12pm on Saturday at the weekend
            // It's not Friday at 3pm because we want people to still appear as accountable on the final report
            $quarterEndDate = $statsReport->quarter->getQuarterEndDate($statsReport->center)->addDay()->setTime(12, 00, 00);

            $isFirstWeek = $statsReport->reportingDate->eq($statsReport->quarter->getFirstWeekDate($statsReport->center));

            $debug_message .= ' sr_id=' . $statsReport->id;

            // Process scoreboard weeks (promises and actuals) and also totals of program leaders
            $debug_message .= $this->submitCenterStatsData($center, $reportingDate, $statsReport);

            // Process applications
            $apps = App::make(Api\Application::class)->allForCenter($center, $reportingDate, true);
            $debug_message .= $this->submitApplications($center, $reportingDate, $statsReport, $apps);

            // Process team members
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
                                          and (coalesce(p.first_name,\'\') != BINARY coalesce(sda.first_name,\'\')
                                                or coalesce(p.last_name,\'\') != BINARY coalesce(sda.last_name,\'\')
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
                                    (team_member_id, at_weekend, xfer_out, xfer_in, wbo, ctw, withdraw_code_id, travel, room, comment,
                                    gitw, tdo, stats_report_id, created_at, updated_at)
                                select ?, atWeekend, xfer_out, xfer_in, wbo, ctw, withdrawCode, travel, room, comment,
                                    gitw, tdo, ?, sysdate(), sysdate()
                                from submission_data_team_members
                                where center_id=? and reporting_date=? and team_member_id=?',
                        [$tm_id, $statsReport->id, $center->id, $reportingDate->toDateString(), $tm_id]);
                    $tmd_id = DB::getPdo()->lastInsertId();
                    $debug_message .= ' last_tmd_id=' . $tmd_id;
                }
            } // end team member processing

            // Insert data rows for any team members that have withdrawn, transfered out or are wbo
            if (!$isFirstWeek) {
                DB::insert('
                    INSERT INTO team_members_data
                        (team_member_id, at_weekend, xfer_out, xfer_in, wbo, ctw, withdraw_code_id,
                        travel, room, comment, gitw, tdo, stats_report_id, created_at, updated_at)
                    SELECT  tmd.team_member_id, tmd.at_weekend, tmd.xfer_out, tmd.xfer_in, tmd.wbo, tmd.ctw,
                            tmd.withdraw_code_id, tmd.travel, tmd.room, tmd.comment, tmd.gitw, tmd.tdo,
                            ?, sysdate(), sysdate()
                    FROM team_members_data tmd
                    INNER JOIN stats_reports sr ON sr.id = tmd.stats_report_id
                    INNER JOIN global_report_stats_report grsr ON grsr.stats_report_id = tmd.stats_report_id
                    WHERE
                        sr.center_id = ?
                        AND sr.reporting_date = ?
                        AND (tmd.withdraw_code_id IS NOT NULL
                            OR tmd.xfer_out = 1
                            OR tmd.wbo = 1)
                        AND tmd.team_member_id NOT IN (SELECT team_member_id FROM team_members_data WHERE stats_report_id = ?)',
                    [$statsReport->id, $center->id, $lastStatsReportDate->toDateString(), $statsReport->id]);
            }

            // Only update the most recently stored PM/CL, ignore any other stashed rows
            foreach (['programManager', 'classroomLeader'] as $accountabilityName) {
                $result = DB::select('
                    SELECT i.*
                    FROM submission_data_program_leaders i
                    LEFT OUTER JOIN people p ON p.id=i.stored_id
                    WHERE i.center_id=? and i.reporting_date=? and i.accountability=?
                    ORDER BY i.id DESC
                    LIMIT 1
                ', [$center->id, $reportingDate->toDateString(), $accountabilityName]);

                if (!$result) {
                    continue;
                }

                $r = $result[0];

                if ($r->stored_id < 0) {
                    // This is a new program leader, create the things
                    DB::insert('
                        INSERT INTO people
                            (first_name, last_name, email, phone, center_id, created_at, updated_at)
                        SELECT i.first_name, i.last_name, i.email, i.phone, i.center_id, sysdate(), sysdate()
                        FROM submission_data_program_leaders i
                        WHERE i.id=?
                    ', [$r->id]);
                    $person_id = DB::getPdo()->lastInsertId();

                    // Update submission_data with new id so we don't overwrite if the report is resubmitted
                    DB::update('UPDATE submission_data SET stored_id=?, data = JSON_SET(data, "$.id", ?) WHERE id=?', [$person_id, $person_id, $r->id]);
                } else {
                    // This is an existing program leader, update the things
                    DB::update('
                        UPDATE people p, submission_data_program_leaders sda
                        SET p.updated_at=sysdate(),
                            p.first_name=sda.first_name,
                            p.last_name=sda.last_name,
                            p.email=sda.email,
                            p.phone=sda.phone,
                            p.updated_at=sysdate()
                        WHERE p.id=sda.stored_id
                            AND sda.id=?
                            AND (coalesce(p.first_name,\'\') != coalesce(sda.first_name,\'\')
                                OR coalesce(p.last_name,\'\') != coalesce(sda.last_name,\'\')
                                OR coalesce(p.email,\'\') != coalesce(sda.email,\'\')
                                OR coalesce(p.phone,\'\') != coalesce(sda.phone,\'\')
                            )
                    ', [$r->id]);

                    $person_id = $r->stored_id;
                }

                $person = Models\Person::find($person_id);
                $accountability = Models\Accountability::name($r->accountability)->first();
                if ($person && $accountability && !$person->hasAccountability($accountability, $reportNow)) {
                    Log::error("Taking over accountability {$r->accountability} for person {$person->id}.");
                    $person->takeoverAccountability($accountability, $reportNow, $quarterEndDate);
                }

                $field = ($accountabilityName == 'programManager') ? 'program_manager_attending_weekend' : 'classroom_leader_attending_weekend';
                DB::update("
                    UPDATE center_stats_data csd, submission_data_program_leaders sd
                    SET csd.{$field} = sd.attending_weekend
                    WHERE sd.id=? AND csd.stats_report_id=? AND csd.reporting_date=? AND csd.type='actual'
                ", [$r->id, $statsReport->id, $reportingDate->toDateString()]);
            } // end program leader processing

            //Insert course data
            $result = DB::select('select i.* from submission_data_courses i
                                    where i.center_id=? and i.reporting_date=?',
                [$center->id, $reportingDate->toDateString()]);
            if (!empty($result)) {
                foreach ($result as $r) {
                    $c_id = -1;
                    if ($r->course_id < 0) {
                        DB::insert('insert into courses
                                        ( id, start_date, type, location, center_id, created_at, updated_at)
                                            select null, i.start_date, i.type, i.location, i.center_id,  sysdate(), sysdate()
                                        from submission_data_courses i where i.id=?',
                            [$r->id]);
                        $c_id = DB::getPdo()->lastInsertId();
                        $debug_message .= ' c_id=' . $c_id;

                        // Update submission_data with new id so we don't overwrite if the report is resubmitted
                        DB::update('update submission_data set stored_id=?, data = JSON_SET(data, "$.id", ?) where id=?', [$c_id, $c_id, $r->id]);
                    } else {
                        // This is an existing course, update the things
                        DB::update('update courses c, submission_data_courses sda
                                    set c.updated_at=sysdate(),
                                        c.start_date=sda.start_date,
                                        c.type=sda.type,
                                        c.location=sda.location
                                    where c.id=sda.course_id
                                          and sda.id=?
                                          and (coalesce(c.start_date,\'\') != coalesce(sda.start_date,\'\')
                                                or coalesce(c.type,\'\') != coalesce(sda.type,\'\')
                                                or coalesce(c.location,\'\') != coalesce(sda.location,\'\'))',
                            [$r->id]);
                        $c_id = $r->course_id;
                    };

                    $affected = DB::insert(
                        'insert into courses_data
                            (course_id, quarter_start_ter, quarter_start_standard_starts, quarter_start_xfer,
                            current_ter, current_standard_starts, current_xfer,
                            completed_standard_starts, potentials, registrations,
                            guests_promised, guests_invited, guests_confirmed, guests_attended,
                            stats_report_id, created_at, updated_at)
                         select course_id, quarter_start_ter, quarter_start_standard_starts,  quarter_start_xfer,
                            current_ter, current_standard_starts, current_xfer,
                            completed_standard_starts, potentials, registrations,
                            guests_promised, guests_invited, guests_confirmed, guests_attended, ?, sysdate(),sysdate()
                         from submission_data_courses
                         where center_id=? and reporting_date=? and course_id=?',
                        [$statsReport->id, $center->id, $reportingDate->toDateString(), $c_id]);
                    $debug_message .= ' upd_courses_rows=' . $affected;
                }
            } // end course processing

            if (!$isFirstWeek) {
                $affected = DB::insert(
                    'insert into courses_data
                        (course_id, quarter_start_ter, quarter_start_standard_starts, quarter_start_xfer,
                        current_ter, current_standard_starts, current_xfer,
                        completed_standard_starts, potentials, registrations,
                        guests_promised, guests_invited, guests_confirmed, guests_attended,
                        stats_report_id, created_at, updated_at)
                     select course_id, quarter_start_ter, quarter_start_standard_starts,  quarter_start_xfer,
                        current_ter, current_standard_starts, current_xfer,
                        completed_standard_starts, potentials, registrations,
                        guests_promised, guests_invited, guests_confirmed, guests_attended, ?, sysdate(),sysdate()
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
                    [$statsReport->id, $lastStatsReportDate->toDateString(), $center->id,
                        $center->id, $reportingDate->toDateString()]);
            }

            // Add/update all accountability holders
            $teamMembers = App::make(Api\TeamMember::class)->allForCenter($center, $reportingDate, true);
            $this->submitTeamAccountabilities($center, $reportingDate, $reportNow, $quarterEndDate, $teamMembers);

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

        if (array_get($data, 'skipSubmitEmail', false) && $this->context->can('skipSubmitEmail', $center)) {
            $emailResults = '<strong>Thank you.</strong> We received your statistics and did not send notification emails.';
        } else {
            $emailResults = $this->sendStatsSubmittedEmail($statsReport);
        }

        $submittedAt = $statsReport->submittedAt->copy()->setTimezone($center->timezone);

        return [
            'success' => $success,
            'id' => $center->id,
            'submittedAt' => $submittedAt->toDateTimeString(),
            'message' => $emailResults,
            'debug_message' => $debug_message,
        ];
    }

    /**
     * Create application objects for submitted statsReport
     *
     * @param  Models\Center      $center
     * @param  Carbon             $reportingDate Report date
     * @param  Models\StatsReport $statsReport   Report to attach objects to
     * @param  array              $apps          Array of Domain\TeamApplication objects
     * @return string                            Debug string
     */
    public function submitApplications(Models\Center $center, Carbon $reportingDate, Models\StatsReport $statsReport, array $apps)
    {
        $debug_message = '';
        foreach ($apps as $app) {
            if ($app->id < 0) {
                // This is a new application so create it
                $application = $this->createNewApplication($center, $app);

                // Now update the stash so subsequent submits don't create new people again
                $this->updateStashIds($center, $reportingDate, 'application', $app->id, $application->id);

                $debug_message .= " sreg_id={$app->id} person_id={$application->person->id}";
            } else {
                // Update application
                $application = $this->updateExistingApplication($center, $app);
            }

            // Crate a new data object for all applications. If new data was stashed, that's included
            // along with last week's data for anyone that wasn't updated
            $appData = Models\TmlpRegistrationData::create([
                'stats_report_id' => $statsReport->id,
                'tmlp_registration_id' => $application->id,
                'incoming_quarter_id' => $app->incomingQuarter->id,
                'reg_date' => $app->regDate,
                'app_out_date' => $app->appOutDate ?: null,
                'app_in_date' => $app->appInDate ?: null,
                'appr_date' => $app->apprDate ?: null,
                'wd_date' => $app->wdDate ?: null,
                'comment' => $app->comment,
                'travel' => (bool) $app->travel,
                'room' => (bool) $app->room,
            ]);
            if ($app->withdrawCode) {
                $appData->withdrawCodeId = $app->withdrawCode->id;
            }
            if ($app->committedTeamMember) {
                $appData->committedTeamMemberId = $app->committedTeamMember->id;
            }
            $appData->save();

            $debug_message .= " reg_id={$application->id} trd_id={$appData->id}";
        }

        return $debug_message;
    }

    /**
     * Create new TmlpRegistration and Person object
     *
     * @param  Models\Center          $center
     * @param  Domain\TeamApplication $app
     * @return Models\TmlpRegistration
     */
    protected function createNewApplication(Models\Center $center, Domain\TeamApplication $app)
    {
        $person = Models\Person::create([
            'center_id' => $center->id,
            'first_name' => $app->firstName,
            'last_name' => $app->lastName,
            'identifier' => '',
        ]);

        return Models\TmlpRegistration::create([
            'person_id' => $person->id,
            'team_year' => $app->teamYear,
            'reg_date' => $app->regDate,
            'is_reviewer' => (bool) $app->isReviewer,
        ]);
    }

    /**
     * Update existing application with data from Domain\TeamApplication
     *
     * @param  Models\Center          $center
     * @param  Domain\TeamApplication $app
     * @return Models\TmlpRegistration
     */
    protected function updateExistingApplication(Models\Center $center, Domain\TeamApplication $app)
    {
        $application = Models\TmlpRegistration::find($app->id);
        if (!$application) {
            // TODO: handle this case
            Log::error("Application {$app->id} not found for update");
            return null;
        }

        // Update application
        $application->teamYear = $app->teamYear;
        $application->regDate = $app->regDate;
        $application->isReviewer = (bool) $app->isReviewer;
        $application->save();

        // Update person
        $person = $application->person;
        $person->centerId = $center->id;
        $person->firstName = $app->firstName;
        $person->lastName = $app->lastName;
        $person->save();

        $application->setRelation('person', $person);

        return $application;
    }

    /**
     * Update stash ids
     *
     * This will update stored_id and data['id'] with the value provided for $newId.
     * Useful for updating stashes after creating new objects so subsequent submits
     * don't create additional objects.
     *
     * @param  Models\Center $center
     * @param  Carbon        $reportingDate
     * @param  string        $type          Stash stored_type
     * @param  string        $stashId       Stash stored_id
     * @param  string        $newId         New value for stroed_id
     * @return boolean
     */
    protected function updateStashIds(Models\Center $center, Carbon $reportingDate, $type, $stashId, $newId)
    {
        // Now update the stash so subsequent submits don't create new people again
        $stash = Models\SubmissionData::centerDate($center, $reportingDate)
            ->typeId($type, $stashId)
            ->first();

        if (!$stash) {
            return false;
        }

        $stash->storedId = $newId;
        $stash->data = array_merge($stash->data, ['id' => $newId]);
        $stash->save();

        return true;
    }

    protected function submitCenterStatsData(Models\Center $center, Carbon $reportingDate, Models\StatsReport $statsReport)
    {
        $debug_message = '';
        // Process scoreboards:
        // Loop through scoreboard weeks and handle appropriately.
        $sbWeeks = App::make(Api\Scoreboard::class)->allForCenter($center, $reportingDate, true, true);
        $teamMemberApi = App::make(Api\TeamMember::class);

        foreach ($sbWeeks->sortedValues() as $scoreboard) {
            if (!array_get($scoreboard->meta, 'localChanges', false)) {
                continue;
            }
            foreach (['promise', 'actual'] as $type) {
                if ($scoreboard->meta['canEdit' . ucfirst($type)]) {
                    $csd = new Models\CenterStatsData([
                        // reporting date in this context is not the date we're doing the report, but the week of the scoreboard in question.
                        'reporting_date' => $scoreboard->week,
                        'stats_report_id' => $statsReport->id,
                        'type' => $type,
                        'points' => $scoreboard->points(),
                    ]);

                    if ($type == 'actual') {
                        list($pmAttending, $clAttending) = $this->calculateProgramLeaderAttending($center, $scoreboard->week);
                        $people = $teamMemberApi->allForCenter($center, $scoreboard->week, true);
                        $csd->programManagerAttendingWeekend = $pmAttending;
                        $csd->classroomLeaderAttendingWeekend = $clAttending;
                        $csd->tdo = $this->calculateTdoFromStashes($people);
                    } else {
                        $csd->tdo = 100;
                    }

                    // loop through to handle handle the 6-games (cap, cpc, etc)
                    foreach ($scoreboard->games() as $gameKey => $game) {
                        $csd->$gameKey = $game->$type(); // metaprogramming: e.g. $csd->cap = $game->promise()
                    }

                    $csd->save();
                    $debug_message .= " csd{$type}={$csd->id}";
                }
            }
        }

        return $debug_message;
    }

    protected function calculateTdoFromStashes($teamMembers)
    {
        $totalMembers = 0;
        $completed = 0;
        foreach ($teamMembers as $tm) {
            if ($tm->xferOut || $tm->withdrawCode !== null || $tm->wbo) {
                continue;
            }
            $totalMembers++;
            $completed += ($tm->tdo) ? 1 : 0;
        }
        if (!$totalMembers) {return 0;}

        return round((100.0 * $completed) / ((float) $totalMembers));
    }

    public function calculateProgramLeaderAttending(Models\Center $center, Carbon $reportingDate)
    {
        $leaders = App::make(Api\ProgramLeader::class)->allForCenter($center, $reportingDate, true);
        $pmAttending = 0;
        $clAttending = 0;

        // This is done due to the limitations of our current storage method.
        // In the future, we should move towards a place we can loop people, allowing situations like multiple classroom leaders (where one's an apprentice or specifically during a weekend of a CL change)
        if (($pmId = $leaders['meta']['programManager']) !== null) {
            $pmAttending += ($leaders[$pmId]->attendingWeekend) ? 1 : 0;
        }

        if (($clId = $leaders['meta']['classroomLeader']) !== null && $clId != $pmId) {
            $clAttending += ($leaders[$clId]->attendingWeekend) ? 1 : 0;
        }

        return [$pmAttending, $clAttending];
    }

    public function submitTeamAccountabilities(Models\Center $center, Carbon $reportingDate, Carbon $reportNow, Carbon $quarterEndDate, $teamMembers)
    {
        // Phase 1: make a map of accountability ID -> person
        $result = [];
        foreach ($teamMembers as $k => $tm) {
            // no idea why we'd have a negative ID at this point, but let's just be safe.
            if ($tm->id > 0 && count($tm->accountabilities)) {
                try {
                    $person = $tm->getAssociatedPerson();
                } catch (\Exception $e) {
                    // TODO send email
                }
                foreach ($tm->accountabilities as $accId) {
                    $result[$accId] = $person;
                }
            }
        }
        // Phase 2: Loop accountabilities (Skip program managers and classroom leaders for now)
        $allAccountabilities = Models\Accountability::context('team')->whereNotIn('id', [8, 9])->get();
        foreach ($allAccountabilities as $accountability) {
            if (!isset($result[$accountability->id])) {
                // No one is listed as accountable, remove any existing accountables
                Models\Accountability::removeAccountabilityFromCenter($accountability->id, $center->id, $reportNow);
            } else {
                $person = $result[$accountability->id];

                // If the person doesn't already have this accountability, add it and remove previous holder
                // Always call takeover to cleanup any stragglers (leftover from spreadsheet migration)
                $person->takeoverAccountability($accountability, $reportNow, $quarterEndDate);
            }
        }

    }

    public function checkCenterDate(Models\Center $center, Carbon $reportingDate, $flags = [])
    {
        if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            throw new Exceptions\BadRequestException('Reporting date must be a Friday.');
        }

        if (!$center->active) {
            throw new Exceptions\BadRequestException('Center is not Active');
        }

        if (in_array('write', $flags)) {

            if ($center->getGlobalRegion()->abbrLower() == 'na' && !$this->context->can('submitOldStats', $center)) {
                if ($reportingDate->lte(Carbon::parse('2017-06-02'))) {
                    // TODO come up with a cleaner solution to this
                    throw new Exceptions\BadRequestException('Cannot do online submission prior to June');
                }
            }
        }

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

        $user = ucfirst($this->context->getUser()->firstName);
        $quarter = $statsReport->quarter;
        $center = $statsReport->center;
        $region = $center->region;
        $reportingDate = $statsReport->reportingDate;

        $submittedAt = $statsReport->submittedAt->copy()->setTimezone($center->timezone);

        $due = $statsReport->due();
        $respondByDateTime = $statsReport->responseDue();

        $isLate = $submittedAt->gt($due);

        $reportNow = $reportingDate->copy()->setTime(15, 0, 0);

        $programManager = $center->getProgramManager($reportNow);
        $classroomLeader = $center->getClassroomLeader($reportNow);
        $t1TeamLeader = $center->getT1TeamLeader($reportNow);
        $t2TeamLeader = $center->getT2TeamLeader($reportNow);
        $statistician = $center->getStatistician($reportNow);
        $statisticianApprentice = $center->getStatisticianApprentice($reportNow);

        $emailMap = [
            'center' => $center->statsEmail,
            'regional' => $region->email,
            'programManager' => $this->getEmail($programManager),
            'classroomLeader' => $this->getEmail($classroomLeader),
            't1TeamLeader' => $this->getEmail($t1TeamLeader),
            't2TeamLeader' => $this->getEmail($t2TeamLeader),
            'statistician' => $this->getEmail($statistician),
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
        $reportUrl = url("/report/{$reportToken->token}");

        $mobileDashUrl = 'https://tmlpstats.com/m/' . strtolower($center->abbreviation);

        $submittedCount = Models\StatsReport::byCenter($center)
            ->reportingDate($statsReport->reportingDate)
            ->submitted()
            ->count();
        $isResubmitted = ($submittedCount > 1);

        $centerName = $center->name;
        $comment = $statsReport->submitComment;
        $reportMessages = App::make(Controllers\StatsReportController::class)->compileApiReportMessages($statsReport);
        try {
            Mail::send('emails.apistatssubmitted',
                compact('centerName', 'comment', 'due', 'isLate', 'isResubmitted', 'mobileDashUrl',
                    'reportingDate', 'reportUrl', 'respondByDateTime', 'submittedAt', 'user', 'reportMessages'),
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
            $successMessage = '<strong>Thank you.</strong> We received your statistics and notified the following emails'
            . " <ul><li>{$emailTo}</li><li>" . implode('</li><li>', $emails) . '</li></ul>'
                . ' Please reply-all to that email if there is anything you need to communicate.';

            if (env('APP_ENV') === 'prod') {
                Log::info("Sent emails to the following people with team {$centerName}'s report: " . implode(', ', $emails));
            } else {
                Log::info("Sent emails to the following people with team {$centerName}'s report: " . env('ADMIN_EMAIL'));
                $successMessage .= '<br/><br/><strong>Since this is development, we sent it to '
                . env('ADMIN_EMAIL') . ' instead.</strong>';
            }

            return $successMessage;
        } catch (\Exception $e) {
            Log::error('Exception caught sending error email: ' . $e->getMessage());

            return 'There was a problem emailing everyone about your stats. Please contact your'
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

    public function initFirstWeekData(Models\Center $center, Models\Quarter $quarter)
    {
        $this->assertCan('copyQuarterData', $center);

        $cq = Domain\CenterQuarter::ensure($center, $quarter);
        // The start weekend is actually in the previous quarter, so we can use that to grab the previous quarter
        $lastWeek = Encapsulations\CenterReportingDate::ensure($center, $cq->startWeekendDate);
        $lastQuarter = $lastWeek->getQuarter();
        $report = [];

        $initData = $this->initSubmission($center, $cq->firstWeekDate);

        // ends up being a map of quarter ID -> random index value which doesn't matter much
        $validStartQids = $initData['validStartQuarters']
            ->map(function ($cq) {return $cq->quarter->id;})
            ->flip();

        // Build a map of team members to next quarter accountabilities
        $nqas = App::make(Api\Submission\NextQtrAccountability::class)->allForCenter($center, $lastWeek->reportingDate);

        $teamNqas = [];
        foreach ($nqas as $nqa) {
            if ($nqa->teamMemberId !== null) {
                $teamNqas[$nqa->teamMemberId][] = $nqa->id;
            }
        }

        // Phase 1: Copy non-completing Team Members
        $goodTeamMembers = [];
        $tmApi = App::make(Api\TeamMember::class);
        $members = $tmApi->allForCenter($center, $lastWeek->reportingDate, true);
        foreach ($members as $id => $member) {
            if ($validStartQids->has($member->incomingQuarterId)
                && $member->withdrawCode == null
                && !$member->xferOut && !$member->wbo) {

                $copy = 'Copied';
                $data = $member->toArray();
                unset($data['tdo'], $data['gitw']);
                $data = array_merge($data, [
                    'xferIn' => false, 'ctw' => false,
                    'travel' => false, 'room' => false,
                    'comment' => '',
                    'quarterNumber' => array_get($data, 'quarterNumber', 0) + 1,
                    'accountabilities' => array_get($teamNqas, $id, []),
                ]);
                $goodTeamMembers[$id] = true;

                $result = $tmApi->stash($center, $cq->firstWeekDate, $data);
                if ($result['success']) {
                    $copy .= ' And stashed';
                }
            } else {
                $copy = 'SKIPPED';
            }

            $report[] = "{$copy} Team Member {$member->id}: {$member->firstName} {$member->lastName}";
        }

        // Phase 2: Copy non-starting Team Expansion
        $appsApi = App::make(Api\Application::class);
        $applications = $appsApi->allForCenter($center, $lastWeek->reportingDate, true);

        foreach ($applications as $id => $app) {
            $personInfo = "{$app->firstName} {$app->lastName} ({$app->id})";
            if ($app->withdrawCode === null) {
                $data = collect($app->toArray())->except(['travel', 'room']);
                if ($app->apprDate !== null && $app->incomingQuarterId == $quarter->id) {
                    // TODO copy applicants to stashed negative ID team members
                    $report[] = "Applicant {$personInfo} should be turned into a team member";
                } else {
                    $data = $data->all(); // ->all() on a collection returns the underlying array
                    if (!array_key_exists($app->committedTeamMemberId, $goodTeamMembers)) {
                        $ctm = $app->committedTeamMember->person;
                        $personInfo .= " NOTE: Had committed team member {$ctm->firstName} {$ctm->lastName} who completed.";
                        unset($data['committedTeamMember']);
                        // $data['comment'] = "AUTOMATED NOTE FROM SYSTEM:\napplicant was copied over from previous quarter's stats. Committed team member {$ctm->firstName} {$ctm->lastName} has completed team. Please pick new committed team member, and then clear this note.";
                    }

                    $appsApi->stash($center, $cq->firstWeekDate, $data);

                    $report[] = "Applicant copied: $personInfo";
                }
            } else {
                $report[] = "SKIPPED withdrawn applicant {$personInfo}";
            }
        }

        // Phase 3: Copy non-completed courses
        $coursesApi = App::make(Api\Course::class);
        $courses = $coursesApi->allForCenter($center, $lastWeek->reportingDate, true);
        foreach ($courses as $id => $course) {
            if (intval($id) > 0 && $course->startDate->gt($lastWeek->reportingDate)) {
                $report[] = "Going to copy course {$course->type} {$course->startDate}";
                $data = array_merge($course->toArray(), [
                    'quarterStartTer' => $course->currentTer,
                    'quarterStartStandardStarts' => $course->currentStandardStarts,
                    'quarterStartXfer' => $course->currentXfer,
                ]);
                $coursesApi->stash($center, $cq->firstWeekDate, $data);
            }
        }

        return compact('report', 'validStartQids');
    }

}

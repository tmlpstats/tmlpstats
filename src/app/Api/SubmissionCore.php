<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Exceptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $validRegQuarters = App::make(Application::class)->validRegistrationQuarters($center, $reportingDate, $quarter);
        $accountabilities = Models\Accountability::orderBy('name')->get();
        $centers = Models\Center::byRegion($center->getGlobalRegion())->active()->orderBy('name')->get();

        return [
            'success' => true,
            'id' => $center->id,
            'validRegQuarters' => $validRegQuarters,
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
                'message' => 'Validation failed'
            ];
        }


		DB::beginTransaction();
		$debug_message = '';
		$person_id=-1;
		$reg_id=-1;
      // Create stats_report record and get id
                   try {
					// Insert into STATS_REPORTS and get id
			       DB::insert('insert into stats_reports ( reporting_date,version,validated,center_id,quarter_id,locked,created_at,updated_at, user_id, submitted_at)
                                           select  reporting_date,\'api\' version,1 validated, center_id,
													quarter_id,\'1\',                 sysdate(),sysdate(), ?, sysdate() from submission_data_scoreboard 
                              				where center_id = ? and reporting_date =?' ,
					[Auth::user()->id, $center->id, $reportingDate->toDateString() ]);
					$sr_id = DB::getPdo()->lastInsertId();
					$debug_message.=' sr_id='.$sr_id;
					// Insert into CENTER_STATS_DATA and get id
					DB::insert('insert into center_stats_data
								(id, reporting_date, type, tdo, cap, cpc, t1x, t2x, gitw,lf,points,
									program_manager_attending_weekend, classroom_leader_attending_weekend,
									stats_report_id, created_at, updated_at)
								select null, reporting_date, type, tdo , cap, cpc, t1x, t2x, gitw, 
									lf, points, null, null, ?, sysdate(), sysdate() 
								from submission_data_scoreboard 
								where center_id = ? and reporting_date= ?',
								[$sr_id,$center->id, $reportingDate->toDateString() ]);
					$cs_id = DB::getPdo()->lastInsertId();
					$debug_message.=' cs_id='.$cs_id;
					// Process applications
					// Loop through all applications ins ubmission data and do the following:
					// - if application is new (stored_id<0), then insert new person, otherwise update info in people table
					// - insert new records into tmlp_
					//
					// ? - Possibly run the "carry over" for all ones that were not changed
					//      by 
					$result = DB::select('select i.* from submission_data_applications i 
											left outer join tmlp_registrations r
												on r.id=i.stored_id
											where i.center_id=?  and i.reporting_date=?;',
											[$center->id, $reportingDate->toDateString()]);
					foreach($result as $r)
					{
						
							if ($r->stored_id<0 ) 
							{
								$rows=DB::insert('insert into  people
												( id, first_name, last_name, email, center_id, identifier, unsubscribed, created_at, updated_at)
													select null, i.first_name, i.last_name, 		i.email, i.center_id, concat(\'r:\',regDate,\':\'), 0, sysdate(), sysdate()
												from submission_data_applications i where i.id=?',
												[$r->id]);
								$person_id = DB::getPdo()->lastInsertId();
								$debug_message.=' sreg_id='.$r->id.' person_id='.$person_id;
								DB::insert('insert into tmlp_registrations
												(person_id, team_year, reg_date, is_reviewer, created_at, updated_at)
												select ?, team_year, 					regDate,isReviewer,sysdate(),sysdate()
												from submission_data_applications i where i.id=?',
												[$person_id,$r->id]);
								$reg_id = DB::getPdo()->lastInsertId();	
								$debug_message.=' reg_id='.$reg_id;								
									DB::update('update submission_data set stored_id=? where id=?',
										[$reg_id,$r->id]);
							}
							else
							{
								// Update PEOPLE table if anything changed 
								DB::update('update people p, submission_data_applications sda
											set p.updated_at=sysdate(),
												p.first_name=sda.first_name,
												p.last_name=sda.last_name,
												p.email=sda.email
											where p.id=sda.person_id
												  and sda.id=?
												  and (coalesce(p.first_name,\'\') != coalesce(sda.first_name,\'\')
														or coalesce(p.last_name,\'\') != coalesce(sda.last_name,\'\')
														or coalesce(p.email,\'\') != coalesce(sda.email,\'\')
												  )',[$r->id] );
								DB::update('update tmlp_registrations p, submission_data_applications sda
											set p.updated_at=sysdate(),
												p.team_year=sda.team_year,
												p.reg_date=sda.regDate,
												p.is_reviewer=sda.isReviewer
											where p.id=sda.stored_id
												  and sda.id=?
												  and (coalesce(p.team_year,\'\') != coalesce(sda.team_year,\'\')
														or coalesce(p.reg_date,\'\') != coalesce(sda.regDate,\'\')
														or coalesce(p.is_reviewer,\'\') != coalesce(sda.isReviewer,\'\'))',[$r->id] );				  
								$person_id=$r->person_id;
								
							};
							
							
							DB::insert('insert into tmlp_registrations_data
										(tmlp_registration_id, reg_date, app_out_date, app_in_date, appr_date, wd_date, 
											withdraw_code_id, committed_team_member_id, incoming_quarter_id, comment, travel, room, stats_report_id, created_at, updated_at)
										select ?, regDate,appOutDate,appinDate,apprDate,wdDate, withdrawCode,committeddteamMember,
										incomingQuarter,comment,travel,room,?, sysdate(),sysdate()
										from submission_data_applications i where i.id=?;',
											[$reg_id,$sr_id,$r->id]);
							$trd_id = DB::getPdo()->lastInsertId();	
							$debug_message.=' trd_id='.$trd_id;
							 
					} // end application processing
					
					// Process team members 
					$affected = DB::insert('insert into team_members_data
					(team_member_id,at_weekend,xfer_out,xfer_in,ctw,withdraw_code_id,travel,room,comment,
							gitw,tdo,stats_report_id, created_at, updated_at)
							select 		team_member_id,atWeekend,xfer_in,xfer_out,ctw,withdrawCode,travel,room,comment,
							gitw,tdo,?,sysdate(),sysdate()
							from submission_data_team_members
							where center_id=? and reporting_date=?',
							[$sr_id,$center->id, $reportingDate->toDateString()]);
					
					$tmd_id = DB::getPdo()->lastInsertId();	
					$debug_message.=' tmd_rows='.$affected.' last_tmd_id='.$tmd_id;
					
					// Link the report in global_report_stats_report, create record in global_report if needed
					
					
					
					$results=DB::query('select id from global_reports where reporting_date=?',[$reportingDate->toDateString()]);
					foreach($result as $r)
					{
						$gr_id=$r->id;
					}
					
					if (!isset($gr_id))
					{
						$affected = DB::insert('insert into global_reports
												(reporting_date,locked,created_at,updated_at)
												values (?,0,sysdate(),sysdate())',
							[$reportingDate->toDateString()]);
					
						$gr_id = DB::getPdo()->lastInsertId();	
					}
					$debug_message.=' gr_id='.$gr_id.' gr_ins='.$affected;
					
					DB::statement('delete from global_report_stats_report where global_report_id=? and stats_report_id in (select id from stats_reports where center_id=? and reporting_date=?)',
					[$gr_id,$center->id,$reportingDate->toDateString()]);
					
					DB::insert('insert into global_report_stats_report
								(stats_report_id, global_report_id,created_at,updated_at)
								  values(?,?,sysdate(),sysdate())
								   ',
								[$sr_id,$gr_id ]);
					} catch (\Exception $e) {
                          return  [
                                  'success' => false,
                                  'id'      => $center->id,
								  'message' => $e -> getMessage(),
								  'debug_message' => $debug_message
						      ];
                     }

        $success = true;
		DB::commit();
        return [
            'success' => $success,
            'id' => $center->id,
			'message' => 'Success',
			'debug_message' => $debug_message
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
}

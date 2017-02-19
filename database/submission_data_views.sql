CREATE or replace VIEW `submission_data_scoreboard` AS
    SELECT 
        'actual' AS `type`,
        a.data->>'$.games.cap.actual' AS `cap`,
        a.data->>'$.games.cpc.actual' AS `cpc`,
        a.data->>'$.games.t1x.actual' AS `t1x`,
        a.data->>'$.games.t2x.actual' AS `t2x`,
        a.data->>'$.games.gitw.actual' AS `gitw`,
        a.data->>'$.games.lf.actual' AS `lf`,
        (((((JSON_EXTRACT(`a`.`data`, '$.games.cap.points') + JSON_EXTRACT(`a`.`data`, '$.games.cpc.points')) + JSON_EXTRACT(`a`.`data`, '$.games.t1x.points')) + JSON_EXTRACT(`a`.`data`, '$.games.t2x.points')) + JSON_EXTRACT(`a`.`data`, '$.games.gitw.points')) + JSON_EXTRACT(`a`.`data`, '$.games.lf.points')) AS `points`,
        null tdo,
        `a`.`id` AS `id`,
        `a`.`center_id` AS `center_id`,
        `a`.`reporting_date` AS `reporting_date`,
        `a`.`stored_type` AS `stored_type`,
        `a`.`stored_id` AS `stored_id`,
        `a`.`user_id` AS `user_id`,
        `a`.`created_at` AS `created_at`,
        `a`.`updated_at` AS `updated_at`,
         b.quarter_id,
        `a`.`data` AS `data`
    FROM
        `submission_data` `a` 
            left outer join 
                 ( select aa.quarter_id, bb.id center_id, aa.start_weekend_date, aa.end_weekend_date
                        from region_quarter_details aa, centers bb, regions cc
                        where 
                        cc.id=bb.region_id and 
                        aa.region_id=coalesce(cc.parent_id,cc.id) ) b
              on a.reporting_date between b.start_weekend_date and b.end_weekend_date
				 and a.center_id=b.center_id
    WHERE
        (`a`.`stored_type` = 'scoreboard_week');
        
CREATE OR REPLACE
VIEW `submission_data_applications` AS
    SELECT 
		`submission_data`.id,
        `submission_data`.center_id,
		`submission_data`.reporting_date,
        `submission_data`.`stored_id` AS `stored_id`,
        `submission_data`.`data`->>'$.firstName' AS `first_name`,
        `submission_data`.`data`->> '$.lastName' AS `last_name`,
        case when 
	 `submission_data`.`data`->> '$.email' = 'null' then NULL
          else  `submission_data`.`data`->> '$.email' end
         AS `email`,
        `submission_data`.`data`->> '$.regDate' AS `regDate`,
        case when 
        `submission_data`.`data`->> '$.isReviewer'='true' then 1
        else 0
        end 
        AS `isReviewer`,
        `submission_data`.`data`->> '$.phone' AS `phone`,
        case when `submission_data`.`data`->> '$.appOutDate' = 'null' then NULL 
            else `submission_data`.`data`->> '$.appOutDate' end
        AS `appOutDate`,
        case when `submission_data`.`data`->> '$.appInDate' ='null' then NULL
          else `submission_data`.`data`->> '$.appInDate' end AS `appinDate`,
        case when `submission_data`.`data`->> '$.apprDate' = 'null' then NULL
              else `submission_data`.`data`->> '$.apprDate' end  AS `apprDate`,
        case when `submission_data`.`data`->> '$.wdDate' = 'null' then NULL
            else  `submission_data`.`data`->> '$.wdDate' end AS `wdDate`,
        `submission_data`.`data`->> '$.teamYear' AS `team_year`,
		`submission_data`.`data`->> '$.incomingQuarter' AS `incomingQuarter`,
        `submission_data`.`data`->> '$.travel' AS `travel`,
        `submission_data`.`data`->> '$.room' AS `room`,
	case when `submission_data`.`data`->> '$.comment'='null' then NULL  
          else `submission_data`.`data`->> '$.comment' end AS `comment`,
        case  
			 when `submission_data`.`data`->>
                        '$.withdrawCode' ='null' then null
             else `submission_data`.`data`->>
                        '$.withdrawCode'
		end 
             AS `withdrawCode`,
        `submission_data`.`data`->>
                        '$.committedTeamMember' AS `committeddteamMember`,
		`tmlp_registrations`.person_id
    FROM
        `submission_data` left outer join `tmlp_registrations`
              on `submission_data`.stored_id=`tmlp_registrations`.id
    WHERE
        (`submission_data`.`stored_type` = 'application');



CREATE OR REPLACE
VIEW `submission_data_team_members` AS
    SELECT 
         id,
         center_id,
         reporting_date,
        `submission_data`.`stored_id` AS `team_member_id`,
        `submission_data`.`data`->> '$.firstName' AS `first_name`,
        `submission_data`.`data`->> '$.lastName' AS `last_name`,
        case when `submission_data`.`data`->> '$.email'='null' then null
                     else `submission_data`.`data`->> '$.email' end  AS `email`,
        case when `submission_data`.`data`->> '$.phone'='null' then null
					else `submission_data`.`data`->> '$.phone'='null' end AS `phone`,
        `submission_data`.`data`->> '$.center' AS `center`,
        `submission_data`.`data`->> '$.teamYear' AS `team_year`,
        `submission_data`.`data`->> '$.incomingQuarter' AS `incoming_quarter_id`,
        case when `submission_data`.`data`->> '$.isReviewer'='true' then 1
					else 0 end AS `is_reviewer`,
        case when `submission_data`.`data`->> '$.atWeekend'='true' then 1 
					else 0 end AS `atWeekend`,
        case when `submission_data`.`data`->> '$.xferIn'='true' then 1 
					else 0 end AS `xfer_in`,
        case when `submission_data`.`data`->> '$.xferOut'='true' then 1 
					else 0 end AS `xfer_out`,
        case when `submission_data`.`data`->> '$.ctw'='true' then 1 
					else 0 end AS `ctw`,
        case when `submission_data`.`data`->> '$.rereg'='true' then 1 
					else 0 end AS `rereg`,
        `submission_data`.`data`->> '$.except' AS `except`,
        case when `submission_data`.`data`->> '$.travel'='true' then 1 
					else 0 end AS `travel`,
        case when `submission_data`.`data`->> '$.room'='true' then 1 
					else 0 end AS `room`,
        case when `submission_data`.`data`->> '$.gitw'='true' then 1 
					else 0 end AS `gitw`,
        case when `submission_data`.`data`->> '$.tdo'='true' then 1 
					else 0 end AS `tdo`,
        case when `submission_data`.`data`->>
                        '$.withdrawCode'='null' then null
                        else `submission_data`.`data`->>
                        '$.withdrawCode' end AS `withdrawCode`,
        case when `submission_data`.`data`->>
                        '$.comment' = 'null' then NULL
              else `submission_data`.`data`->> '$.comment' end AS `comment`,
		`submission_data`.`data`->>
                        '$.accountabilities' AS `accountabilities`
    FROM
        `submission_data`
    WHERE
        (`submission_data`.`stored_type` = 'team_member');
        
CREATE OR REPLACE VIEW submission_data_accountabilities AS
    SELECT 
        a.id,
        a.center_id,
        a.reporting_date,
        accountabilities.id accountability_id,
        a.team_member_id,
        t.person_id
    FROM
        submission_data_team_members a,
        accountabilities,
        team_members t
    WHERE
       json_contains(a.accountabilities,concat('"',accountabilities.id,'"'))=1
       and t.id=a.team_member_id;


CREATE OR REPLACE
VIEW `submission_data_courses` AS
    SELECT 
         id,
         center_id,
         reporting_date,
        `submission_data`.`data`->> '$.startDate' AS `start_date`,
        `submission_data`.`data`->> '$.type' AS `type`,
         case when `submission_data`.`data`->> '$.location' = 'null' then NULL
              else `submission_data`.`data`->> '$.location' end AS `location`,
        `submission_data`.`stored_id` AS `course_id`,
        `submission_data`.`data`->> '$.quarterStartTer' AS `quarter_start_ter`,
        `submission_data`.`data`->> '$.quarterStartStandardStarts' AS `quarter_start_standard_starts`,
        `submission_data`.`data`->> '$.quarterStartXfer' AS `quarter_start_xfer`,
        `submission_data`.`data`->> '$.currentTer' AS `current_ter`,
        `submission_data`.`data`->> '$.currentStandardStarts' AS `current_standard_starts`,
        `submission_data`.`data`->> '$.currentXfer' AS `current_xfer`,
        `submission_data`.`data`->> '$.completedStandardStarts' AS `completed_standard_starts`,
        `submission_data`.`data`->> '$.potentials' AS `potentials`,
        `submission_data`.`data`->> '$.registrations' AS `registrations`,
        case when `submission_data`.`data`->> '$.guestsPromised'='null' then NULL
             else `submission_data`.`data`->> '$.guestsPromised' end  AS `guests_promised`,
        case when `submission_data`.`data`->> '$.guestsInvited' ='null' then NULL 
             else `submission_data`.`data`->> '$.guestsInvited' end AS `guests_invited`,
        case when `submission_data`.`data`->> '$.guestsConfirmed' ='null' then NULL
             else `submission_data`.`data`->> '$.guestsConfirmed' end AS `guests_confirmed`,
        case when `submission_data`.`data`->> '$.guestsAttended' = 'null' then NULL 
             else `submission_data`.`data`->> '$.guestsAttended' end AS `guests_attended`
    FROM
        `submission_data`
    WHERE
        (`submission_data`.`stored_type` = 'course');

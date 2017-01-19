CREATE or replace VIEW `submission_data_scoreboard` AS
    SELECT 
        'actual' AS `type`,
        JSON_UNQUOTE(JSON_EXTRACT(`a`.`data`, '$.games.cap.actual')) AS `cap`,
        JSON_UNQUOTE(JSON_EXTRACT(`a`.`data`, '$.games.cpc.actual')) AS `cpc`,
        JSON_UNQUOTE(JSON_EXTRACT(`a`.`data`, '$.games.t1x.actual')) AS `t1x`,
        JSON_UNQUOTE(JSON_EXTRACT(`a`.`data`, '$.games.t2x.actual')) AS `t2x`,
        JSON_UNQUOTE(JSON_EXTRACT(`a`.`data`, '$.games.gitw.actual')) AS `gitw`,
        JSON_UNQUOTE(JSON_EXTRACT(`a`.`data`, '$.games.lf.actual')) AS `lf`,
        (((((JSON_EXTRACT(`a`.`data`, '$.games.cap.points') + JSON_EXTRACT(`a`.`data`, '$.games.cpc.points')) + JSON_EXTRACT(`a`.`data`, '$.games.t1x.points')) + JSON_EXTRACT(`a`.`data`, '$.games.t2x.points')) + JSON_EXTRACT(`a`.`data`, '$.games.gitw.points')) + JSON_EXTRACT(`a`.`data`, '$.games.lf.points')) AS `points`,
        `a`.`id` AS `id`,
        `a`.`center_id` AS `center_id`,
        `a`.`reporting_date` AS `reporting_date`,
        `a`.`stored_type` AS `stored_type`,
        `a`.`stored_id` AS `stored_id`,
        `a`.`user_id` AS `user_id`,
        `a`.`created_at` AS `created_at`,
        `a`.`updated_at` AS `updated_at`,
        `a`.`data` AS `data`
    FROM
        `submission_data` `a`
    WHERE
        (`a`.`stored_type` = 'scoreboard_week');
        
CREATE OR REPLACE
VIEW `submission_data_applications` AS
    SELECT 
		`submission_data`.id,
        `submission_data`.center_id,
		`submission_data`.reporting_date,
        `submission_data`.`stored_id` AS `stored_id`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.firstName')) AS `first_name`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.lastName')) AS `last_name`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.email')) AS `email`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.regDate')) AS `regDate`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.isReviewer')) AS `isReviewer`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.phone')) AS `phone`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.appOutDate')) AS `appOutDate`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.appInDate')) AS `appinDate`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.apprDate')) AS `apprDate`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.wdDate')) AS `wdDate`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.teamYear')) AS `team_year`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`,
                        '$.withdrawCode')) AS `withdrawCode`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`,
                        '$.committedTeamMember')) AS `committeddteamMember`,
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
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.firstName')) AS `first_name`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.lastName')) AS `last_name`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.email')) AS `email`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.phone')) AS `phone`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.center')) AS `center`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.teamYear')) AS `team_year`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.incomingQuarter')) AS `incoming_quarter_id`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.isReviewer')) AS `is_reviewer`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.atWeekend')) AS `atWeekend`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.xferIn')) AS `xfer_in`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.xferOut')) AS `xfer_out`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.ctw')) AS `ctw`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.rereg')) AS `rereg`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.except')) AS `except`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.travel')) AS `travel`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.room')) AS `room`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.gitw')) AS `gitw`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.tdo')) AS `tdo`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`,
                        '$.withdrawCode')) AS `withdrawCode`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`,
                        '$.comment')) AS `comment`,
		JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`,
                        '$.accountabilities')) AS `accountabilities`
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
VIEW `submission_data_team_members` AS
    SELECT 
         id,
         center_id,
         reporting_date,
        `submission_data`.`id` AS `team_member_id`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.startDate')) AS `start_date`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.type')) AS `type`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.location')) AS `location`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.id')) AS `course_id`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.quarterStartTer')) AS `quarter_start_ter`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.quarterStartStandardStarts')) AS `quarter_start_standard_starts`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.quarterStartXfer')) AS `quarter_start_xfer`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.currentTer')) AS `current_ter`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.currentStandardStarts')) AS `current_standard_starts`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.currentXfer')) AS `current_xfer`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.completedStandardStarts')) AS `completed_standard_starts`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.potentials')) AS `potentials`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.registrations')) AS `registrations`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.guestsPromised')) AS `guests_promised`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.guestsInvited')) AS `guests_invited`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.guestsConfirmed')) AS `guests_confirmed`,
        JSON_UNQUOTE(JSON_EXTRACT(`submission_data`.`data`, '$.guestsAttended')) AS `guests_ttended`
    FROM
        `submission_data`
    WHERE
        (`submission_data`.`stored_type` = 'course');

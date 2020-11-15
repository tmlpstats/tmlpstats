<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TMLP Stats Validation Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during statistic report validation
    |
    */

    'CENTERGAME_CAP_ACTUAL_INCORRECT' => 'You reported :reported for the CAP actual, but the net number of CAP registrations based on course numbers is :calculated. Please correct the discrepancy before submitting.',
    'CENTERGAME_CPC_ACTUAL_INCORRECT' => 'You reported :reported for the CPC actual, but the net number of CPC registrations based on course numbers is :calculated. Please correct the discrepancy before submitting.',
    'CENTERGAME_GITW_ACTUAL_INCORRECT' => 'You reported :reported for the GITW actual, but the percentage of team members reported as effective is :calculated.',
    'CENTERGAME_T1X_ACTUAL_INCORRECT' => 'You reported :reported for the T1X actual, but the net number of T1 incoming approved based on team expansion numbers is :calculated. Please correct the discrepancy before submitting.',
    'CENTERGAME_T2X_ACTUAL_INCORRECT' => 'You reported :reported for the T2X actual, but the net number of T2 incoming approved based on team expansion numbers is :calculated. Please correct the discrepancy before submitting.',
    'CLASSLIST_ACCOUNTABLE_AND_WITHDRAWN' => 'Team member has left the team. Please remove accountabilities.',
    'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING' => 'Team member is the :accountability accountable. Please provide their email address in case we need to contact them regarding statistics.',
    'CLASSLIST_ACCOUNTABLE_PHONE_MISSING' => 'Team member is the :accountability accountable. Please provide their phone number in case we need to contact them regarding statistics.',
    'CLASSLIST_BOUNCED_EMAIL' => 'The email provided (:email) is not reachable. Please correct it.',
    'CLASSLIST_CTW_WBO_COMMENT_MISSING' => 'Add a comment with the issue/concern. Please be general enough that the participant\'s personal privacy is respected.',
    'CLASSLIST_GITW_MISSING' => 'GITW was not provided.',
    'CLASSLIST_MISSING_ACCOUNTABLE' => 'No one was is listed as :accountability accountable. If this is correct, let your regional statistician know.',
    'CLASSLIST_MULTIPLE_ACCOUNTABLES' => 'Multiple team members have accountability :accountability. Please provide no more than 1 person.',
    'CLASSLIST_ROOM_COMMENT_MISSING' => 'Either rooming must be booked and marked with a Y under Room Booked, or add a comment providing a specific promise of action with a "by when" date and time.',
    'CLASSLIST_ROOM_COMMENT_REVIEW' => 'Rooming is not booked. Please add a comment providing a specific promise of action with a "by when" date and time.',
    'CLASSLIST_TDO_MISSING' => 'TDO was not provided.',
    'CLASSLIST_TRAVEL_COMMENT_MISSING' => 'Either travel must be booked and marked with a Y under Travel Booked, or add a comment providing a specific promise of action with a "by when" date and time.',
    'CLASSLIST_TRAVEL_COMMENT_REVIEW' => 'Travel is not booked. Please add a comment providing a specific promise of action with a "by when" date and time.',
    'CLASSLIST_UNKNOWN_ACCOUNTABILITY' => 'Unrecognized accountability (:accountabilityId) provided.',
    'CLASSLIST_WD_CODE_INACTIVE' => 'Withdraw reason :reason is not available. Please choose another reason.',
    'CLASSLIST_WD_CODE_UNKNOWN' => 'Unrecognized withdraw code.',
    'CLASSLIST_WD_CODE_WRONG_CONTEXT' => 'Withdraw reason :reason is not available for team members. Please choose another reason.',
    'CLASSLIST_WD_COMMENT_MISSING' => 'Add a comment with the date of withdraw.',
    'CLASSLIST_WD_CTW_ONLY_ONE' => 'Both WD/WBI and CTW are set. CTW should not be set after the team member has withdrawn or taken a leave for WBI.',
    'CLASSLIST_WKND_MISSING' => 'Was team member at the weekend, or did they transfer in after the weekend? Please select one.',
    'CLASSLIST_WKND_XIN_REREG_ONLY_ONE' => 'Was team member at the weekend, or did they transfer in after the weekend? Please select only one.',
    'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER' => 'Team member is transferring. Confirm with other center that team member is reported appropriately on their report.',
    'CLASSLIST_XFER_COMMENT_MISSING' => 'Add a comment specifying the date of transfer and the center they transferred to/from.',
    'CLASSLIST_XFER_ONLY_ONE' => 'Person cannot transfer in and out at the same time.',
    'COURSE_COMPLETED_REGISTRATIONS_GREATER_THAN_POTENTIALS' => 'Registrations (:registrations) cannot be greater than the number of potentials for the course (:potentials). Please confirm what the correct values are with the course supervisor and/or your program manager.',
    'COURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS' => 'More people completed the course than started. Make sure Current Standard Starts matches the number of people that started the course, and Completed Standard Starts matches the number of people that completed the course.',
    'COURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS' => 'Completed Standard Starts is :delta less than the course starting standard starts. Please confirm with your program manager that :delta people did withdraw during the course, and put in a comment to let us know.',
    'COURSE_COMPLETED_SS_MISSING' => 'Course is missing completion statistic: Standard Starts Completed.',
    'COURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE' => 'Course has not completed, but has completion stats.',
    'COURSE_COURSE_DATE_BEFORE_QUARTER' => 'Course occurred before this quarter started.',
    'COURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER' => 'Current Standard Starts (:starts) cannot be more than the total number of people ever registered in the course (:ter).',
    'COURSE_CURRENT_TER_LESS_THAN_QSTART_TER' => 'Current Total Ever Registered (:currentTer) is less than Quarter Starting Total Ever Registered (:quarterStartTer). Please verify this is accurate with Program Manager. This could happen due to a registration error. Please put in a comment with the what\'s so.',
    'COURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER' => 'Current Transfer in from Earlier (:xfer) cannot be more than the total number of people ever registered in the course (:ter).',
    'COURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER' => 'Current Transfer in from Earlier (:currentXfer) is less than the Quarter Starting Transfer in from Earlier (:quarterStartXfer). This should only be possible if someone who previously transferred was withdrawn as a registration error. Please verify this is accurate with Program Manager. Please put in a comment that this change is verified.',
    'COURSE_GUESTS_ATTENDED_MISSING' => 'Course has completed but the transforming lives games\'s number of people who attended is missing.',
    'COURSE_GUESTS_ATTENDED_PROVIDED_BEFORE_COURSE' => 'Course has not completed yet, but the transforming lives games shows that people have already attended.',
    'COURSE_GUESTS_CONFIRMED_MISSING' => 'Course has completed but the transforming lives games\'s number of people confirmed is missing.',
    'COURSE_GUESTS_INVITED_MISSING' => 'Course has completed but the transforming lives games\'s number of people invited is missing.',
    'COURSE_POTENTIALS_MISSING' => 'Course is missing completion statistic: Potentials.',
    'COURSE_QSTART_SS_CHANGED' => 'Course quarter starting standard starts (SS) changed from :was to :now. Please put in a comment when you submit with what happened that led to this change.',
    'COURSE_QSTART_SS_DOES_NOT_MATCH_QEND' => 'Course quarter starting standard starts (SS) does not match the value from the end of last quarter. It changed from :was to :now. Please put in comment when you submit with what happened that led to this change.',
    'COURSE_QSTART_SS_GREATER_THAN_QSTART_TER' => 'Quarter Starting Standard Starts (:starts) cannot be more than the quarter starting total number of people ever registered in the course (:ter).',
    'COURSE_QSTART_TER_CHANGED' => 'Course quarter starting total ever registered (TER) changed from :was to :now. Please put in comment when you submit with what happened that led to this change.',
    'COURSE_QSTART_TER_DOES_NOT_MATCH_QEND' => 'Course quarter starting total ever registered (TER) does not match the value from the end of last quarter. It changed from :was to :now. Please put in comment when you submit with what happened that led to this change.',
    'COURSE_QSTART_XFER_CHANGED' => 'Course quarter starting transferred in changed from :was to :now. Please put in a comment when you submit with what happened that led to this change.',
    'COURSE_QSTART_XFER_DOES_NOT_MATCH_QEND' => 'Course quarter starting transferred in does not match the value from the end of last quarter. It changed from :was to :now. Please put in comment when you submit with what happened that led to this change.',
    'COURSE_QSTART_XFER_GREATER_THAN_QSTART_TER' => 'Quarter Starting Transfer in from Earlier (:xfer) cannot be more than the quarter starting total number of people ever registered in the course (:ter).',
    'COURSE_REGISTRATIONS_MISSING' => 'Course is missing completion statistic: Registrations.',
    'COURSE_START_DATE_CHANGED' => 'Course start date changed from :was to :now. Please put in a comment when you submit with whagot t happened that led to this change.',
    'COURSE_START_DATE_NOT_SATURDAY' => 'Course does not start on a Saturday. Please check that this is correct.',
    'GENERAL_COMMENT_TOO_LONG' => 'Comment is :currentLength characters. It must be less than :maxLength characters.',
    'GENERAL_INVALID_VALUE' => 'Incorrect value provided for :name (:value).',
    'GENERAL_MISSING_VALUE' => ':name is required, but no value was provided.',
    'PROGRAMLEADER_BOUNCED_EMAIL' => 'The email provided for :accountability (:email) is not reachable. Please correct it.',
    'TEAMAPP_APPIN_DATE_BEFORE_APPOUT_DATE' => 'App in date is before app out date.',
    'TEAMAPP_APPIN_DATE_BEFORE_REG_DATE' => 'App in date is before registration date.',
    'TEAMAPP_APPIN_DATE_CHANGED' => 'Application in date changed from :was to :now.',
    'TEAMAPP_APPIN_DATE_IN_FUTURE' => 'Application In date is in the future.',
    'TEAMAPP_APPIN_DATE_MISSING' => 'Missing App In date.',
    'TEAMAPP_APPIN_LATE' => 'Application was not returned within :daysSince days of sending application out. Application is out of integrity with the design of the application process.',
    'TEAMAPP_APPOUT_DATE_BEFORE_REG_DATE' => 'App out date is before registration date.',
    'TEAMAPP_APPOUT_DATE_CHANGED' => 'Application out date changed from :was to :now.',
    'TEAMAPP_APPOUT_DATE_IN_FUTURE' => 'Application Out date is in the future.',
    'TEAMAPP_APPOUT_DATE_MISSING' => 'Missing App Out date.',
    'TEAMAPP_APPOUT_LATE' => 'Application was not sent to applicant within :daysSince days of registration. Application is out of integrity with the design of the application process.',
    'TEAMAPP_APPR_DATE_BEFORE_APPIN_DATE' => 'Approval date is before app in date.',
    'TEAMAPP_APPR_DATE_BEFORE_APPOUT_DATE' => 'Approval date is before app out date.',
    'TEAMAPP_APPR_DATE_BEFORE_REG_DATE' => 'Approval date is before registration date.',
    'TEAMAPP_APPR_DATE_CHANGED' => 'Approval date changed from :was to :now.',
    'TEAMAPP_APPR_DATE_IN_FUTURE' => 'Approve date is in the future.',
    'TEAMAPP_APPR_LATE' => 'Approval process was not completed within :daysSince days of returned application. Application is out of integrity with the design of the application process.',
    'TEAMAPP_APPR_LATE2' => 'Approval process was not completed within :daysSince days of registration. The application is out of integrity with the design of the application process.',
    'TEAMAPP_BOUNCED_EMAIL' => 'The email provided (:email) is not reachable. Please correct it.',
    'TEAMAPP_INCOMING_QUARTER_CHANGED' => 'Applicant incoming quarter was :was, and is now :now. Ensure that applicant is within integrity of application process (only transferred once to a future quarter after the application is approved).',
    'TEAMAPP_NO_COMMITTED_TEAM_MEMBER' => 'No committed team member provided.',
    'TEAMAPP_REG_DATE_CHANGED' => 'Registration date changed from :was to :now.',
    'TEAMAPP_REG_DATE_IN_FUTURE' => 'Registration date is in the future.',
    'TEAMAPP_REVIEWER_TEAM1' => 'Only Team 2 can review. Please check that the team year and reviewer status are correct.',
    'TEAMAPP_ROOM_COMMENT_MISSING' => 'Either rooming must be booked and marked with a Y under Room Booked, or add a comment providing a specific promise of action with a "by when" date and time.',
    'TEAMAPP_ROOM_COMMENT_REVIEW' => 'Rooming is not booked. Please add a comment providing a specific promise of action with a "by when" date and time.',
    'TEAMAPP_TRAVEL_COMMENT_MISSING' => 'Either travel must be booked and marked with a Y under Travel Booked, or add a comment providing a specific promise of action with a "by when" date and time.',
    'TEAMAPP_TRAVEL_COMMENT_REVIEW' => 'Travel is not booked. Please add a comment providing a specific promise of action with a "by when" date and time.',
    'TEAMAPP_WD_CODE_INACTIVE' => 'Withdraw reason :reason is not available. Please choose another reason.',
    'TEAMAPP_WD_CODE_MISSING' => 'Missing reason for withdraw.',
    'TEAMAPP_WD_CODE_UNKNOWN' => 'Unrecognized withdraw code.',
    'TEAMAPP_WD_CODE_WRONG_CONTEXT' => 'Withdraw reason :reason is not available for applications. Please choose another reason.',
    'TEAMAPP_WD_DATE_BEFORE_APPIN_DATE' => 'Withdraw date is before app in date.',
    'TEAMAPP_WD_DATE_BEFORE_APPOUT_DATE' => 'Withdraw date is before app out date.',
    'TEAMAPP_WD_DATE_BEFORE_APPR_DATE' => 'Withdraw date is before approval date.',
    'TEAMAPP_WD_DATE_BEFORE_APPR_DATE' => 'Withdraw date is before approval date.',
    'TEAMAPP_WD_DATE_BEFORE_REG_DATE' => 'Withdraw date is before registration date.',
    'TEAMAPP_WD_DATE_CHANGED' => 'Withdraw date changed from :was to :now.',
    'TEAMAPP_WD_DATE_IN_FUTURE' => 'Withdraw date is in the future.',
    'TEAMAPP_WD_DATE_MISSING' => 'Missing withdraw date.',
    'VALDATA_NOT_UPDATED' => ':type has not been updated since last week. Please confirm that no changes are needed.',
    'ZZZ_TEST_MESSAGE_0_PARAM' => 'This message has 0 params.',
    'ZZZ_TEST_MESSAGE_1_PARAM' => 'This message has 1 param: :one.',
    'ZZZ_TEST_MESSAGE_2_PARAM' => 'This message has 2 params: :one and :two.',
];

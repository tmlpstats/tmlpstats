<?php
namespace TmlpStats;

use Respect\Validation\Validator as v;

class Message
{
    const EMERGENCY = 1;
    const ALERT     = 2;
    const CRITICAL  = 3;
    const ERROR     = 4;
    const WARNING   = 5;
    const NOTICE    = 6;
    const INFO      = 7;
    const DEBUG     = 8;

    // Message Definitions
    static $messageList = array(
        // General Errors
        'INVALID_VALUE' => array(
            'type' => Message::ERROR,
            'format' => "Incorrect value provided for %%display_name%% ('%%value%%').",
            'arguments' => array(
                '%%display_name%%',
                '%%value%%'
            ),
        ),
        'IMPORT_TAB_FAILED' => array(
            'type' => Message::ERROR,
            'format' => "Unable to import tab.",
            'arguments' => array(),
        ),
        'EXCEPTION_LOADING_ENTRY' => array(
            'type' => Message::ERROR,
            'format' => "There was an error processing tab: %%message%%.",
            'arguments' => array(
                '%%message%%',
            ),
        ),

        // TMLP Registration Validator Errors
        'TMLPREG_MULTIPLE_WEEKENDREG' => array(
            'type' => Message::ERROR,
            'format' => "Weekend Reg section contains multiple %%incomingTeamYear%%'s. Only one should be provided",
            'arguments' => array(
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_WD_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Withdraw date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_WD_DATE_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "No withdraw date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_WD_DOESNT_MATCH_INCOMING_YEAR' => array(
            'type' => Message::ERROR,
            'format' => "The program year specified for WD doesn't match the incoming program year. It should match the value in the Weekend Reg columns",
            'arguments' => array(),
        ),
        'TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR' => array(
            'type' => Message::ERROR,
            'format' => "If person has withdrawn, only column '%%offset%%' should contain a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPR_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Approved date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPR_DATE_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "No approved date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR' => array(
            'type' => Message::ERROR,
            'format' => "If person is approved, only column '%%offset%%' should contain a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPR_MISSING_APPIN_DATE' => array(
            'type' => Message::ERROR,
            'format' => "No app in date provided",
            'arguments' => array(),
        ),
        'TMLPREG_APPR_MISSING_APPOUT_DATE' => array(
            'type' => Message::ERROR,
            'format' => "No app out date provided",
            'arguments' => array(),
        ),
        'TMLPREG_APPIN_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "App in date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPIN_DATE_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "No app in date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPIN_ONLY_ONE_YEAR_INDICATOR' => array(
            'type' => Message::ERROR,
            'format' => "If person's application is in, only column '%%offset%%' should contain a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPIN_MISSING_APPOUT_DATE' => array(
            'type' => Message::ERROR,
            'format' => "No app out date provided",
            'arguments' => array(),
        ),
        'TMLPREG_APPOUT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "App out date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_APPOUT_DATE_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "No app out date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => array(
                '%%offset%%',
                '%%incomingTeamYear%%',
            ),
        ),
        'TMLPREG_NO_COMMITTED_TEAM_MEMBER' => array(
            'type' => Message::ERROR,
            'format' => "No committed team member provided",
            'arguments' => array(),
        ),
        'TMLPREG_WD_DATE_BEFORE_REG_DATE' => array(
            'type' => Message::ERROR,
            'format' => "Withdraw date is before registration date",
            'arguments' => array(),
        ),
        'TMLPREG_WD_DATE_BEFORE_APPR_DATE' => array(
            'type' => Message::ERROR,
            'format' => "Withdraw date is before approval date",
            'arguments' => array(),
        ),
        'TMLPREG_WD_DATE_BEFORE_APPIN_DATE' => array(
            'type' => Message::ERROR,
            'format' => "Withdraw date is before app in date",
            'arguments' => array(),
        ),
        'TMLPREG_WD_DATE_BEFORE_APPOUT_DATE' => array(
            'type' => Message::ERROR,
            'format' => "Withdraw date is before app out date",
            'arguments' => array(),
        ),
        'TMLPREG_APPR_DATE_BEFORE_REG_DATE' => array(
            'type' => Message::ERROR,
            'format' => "Approval date is before registration date",
            'arguments' => array(),
        ),
        'TMLPREG_APPR_DATE_BEFORE_APPIN_DATE' => array(
            'type' => Message::ERROR,
            'format' => "Approval date is before app in date",
            'arguments' => array(),
        ),
        'TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE' => array(
            'type' => Message::ERROR,
            'format' => "Approval date is before app out date",
            'arguments' => array(),
        ),
        'TMLPREG_APPIN_DATE_BEFORE_REG_DATE' => array(
            'type' => Message::ERROR,
            'format' => "App in date is before registration date",
            'arguments' => array(),
        ),
        'TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE' => array(
            'type' => Message::ERROR,
            'format' => "App in date is before app out date",
            'arguments' => array(),
        ),
        'TMLPREG_APPOUT_DATE_BEFORE_REG_DATE' => array(
            'type' => Message::ERROR,
            'format' => "App out date is before registration date",
            'arguments' => array(),
        ),
        'TMLPREG_BEF_REG_DATE_NOT_BEFORE_WEEKEND' => array(
            'type' => Message::ERROR,
            'format' => "Registration is not before the quarter's start date (%%date%%) but has a %%value%% in Bef column",
            'arguments' => array(
                '%%date%%',
                '%%value%%',
            ),
        ),
        'TMLPREG_DUR_REG_DATE_NOT_DURING_WEEKEND' => array(
            'type' => Message::ERROR,
            'format' => "Registration date is not during quarter start weekend (%%date%%) but has a %%value%% in Dur column",
            'arguments' => array(
                '%%date%%',
                '%%value%%',
            ),
        ),
        'TMLPREG_AFT_REG_DATE_NOT_AFTER_WEEKEND' => array(
            'type' => Message::ERROR,
            'format' => "Registration date is not after quarter start date (%%date%%) but has a %%value%% in Aft column",
            'arguments' => array(
                '%%date%%',
                '%%value%%',
            ),
        ),
        'TMLPREG_APPOUT_LATE' => array(
            'type' => Message::WARNING,
            'format' => "Application was not sent to applicant within %%daysSince%% days of registration.",
            'arguments' => array(
                '%%daysSince%%',
            ),
        ),
        'TMLPREG_APPIN_LATE' => array(
            'type' => Message::WARNING,
            'format' => "Application not returned within %%daysSince%% days since sending application out. Application is not in integrity with design of application process.",
            'arguments' => array(
                '%%daysSince%%',
            ),
        ),
        'TMLPREG_APPR_LATE' => array(
            'type' => Message::WARNING,
            'format' => "Application not approved within %%daysSince%% days since sending application out.",
            'arguments' => array(
                '%%daysSince%%',
            ),
        ),
        'TMLPREG_REG_DATE_IN_FUTURE' => array(
            'type' => Message::ERROR,
            'format' => "Registration date is in the future. Please check date.",
            'arguments' => array(),
        ),
        'TMLPREG_WD_DATE_IN_FUTURE' => array(
            'type' => Message::ERROR,
            'format' => "Withdraw date is in the future. Please check date.",
            'arguments' => array(),
        ),
        'TMLPREG_APPR_DATE_IN_FUTURE' => array(
            'type' => Message::ERROR,
            'format' => "Approve date is in the future. Please check date.",
            'arguments' => array(),
        ),
        'TMLPREG_APPIN_DATE_IN_FUTURE' => array(
            'type' => Message::ERROR,
            'format' => "Application In date is in the future. Please check date.",
            'arguments' => array(),
        ),
        'TMLPREG_APPOUT_DATE_IN_FUTURE' => array(
            'type' => Message::ERROR,
            'format' => "Application Out date is in the future. Please check date.",
            'arguments' => array(),
        ),
        'TMLPREG_COMMENT_MISSING_FUTURE_WEEKEND' => array(
            'type' => Message::ERROR,
            'format' => "No comment provided specifying incoming weekend for future registration",
            'arguments' => array(),
        ),
        'TMLPREG_TRAVEL_COMMENT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Either travel must be complete and marked with a Y in the Travel column, or a comment with a specific promise must be provided",
            'arguments' => array(),
        ),
        'TMLPREG_ROOM_COMMENT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Either rooming must be complete and marked with a Y in the Room column, or a comment with a specific promise must be provided",
            'arguments' => array(),
        ),
        'TMLPREG_TRAVEL_COMMENT_REVIEW' => array(
            'type' => Message::WARNING,
            'format' => "Travel is not booked. Make sure the comment provides a specific promise for when travel will be complete.",
            'arguments' => array(),
        ),
        'TMLPREG_ROOM_COMMENT_REVIEW' => array(
            'type' => Message::WARNING,
            'format' => "Rooming is not booked. Make sure the comment provides a specific promise for when rooming will be complete.",
            'arguments' => array(),
        ),
        'TMLPREG_TRAVEL_ROOM_CTW_COMMENT_REVIEW' => array(
            'type' => Message::WARNING,
            'format' => "Travel/rooming are not complete by 2 weeks before the end of the quarter. Make sure the comment states that they are in a Conversation To Withdraw (CTW).",
            'arguments' => array(),
        ),

        // TMLP Course Info Errors
        'TMLPCOURSE_QSTART_TER_LESS_THAN_QSTART_APPROVED' => array(
            'type' => Message::ERROR,
            'format' => "Quarter Starting Total Registered is less than Quarter Starting Total Approved. Starting Registered should include Approved applications.",
            'arguments' => array(),
        ),

        // Communication Course Info Errors
        'COMMCOURSE_COMPLETED_SS_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Course has completed but is missing Standard Starts Completed",
            'arguments' => array(),
        ),
        'COMMCOURSE_POTENTIALS_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Course has completed but is missing Potentials",
            'arguments' => array(),
        ),
        'COMMCOURSE_REGISTRATIONS_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Course has completed but is missing Registrations",
            'arguments' => array(),
        ),
        'COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE' => array(
            'type' => Message::ERROR,
            'format' => "Course Completion stats provided, but course has not completed.",
            'arguments' => array(),
        ),
        'COMMCOURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS' => array(
            'type' => Message::WARNING,
            'format' => "Completed Standard Starts is %%difference%% less than the course starting standard starts. Confirm with your regional statistician that %%difference%% people did withdraw during the course.",
            'arguments' => array(
                '%%difference%%',
            ),
        ),
        'COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS' => array(
            'type' => Message::ERROR,
            'format' => "More people completed the course than there were that started. Make sure Current Standard Starts matches the number of people that started the course, and Completed Standard Starts matches the number of people that completed the course.",
            'arguments' => array(),
        ),
        'COMMCOURSE_COURSE_DATE_BEFORE_QUARTER' => array(
            'type' => Message::ERROR,
            'format' => "Course occured before quarter started",
            'arguments' => array(),
        ),
        'COMMCOURSE_QSTART_SS_GREATER_THAN_QSTART_TER' => array(
            'type' => Message::ERROR,
            'format' => "Quarter Starting Standard Starts (%%starts%%) cannot be more than the quarter starting total number of people ever registered in the course (%%ter%%)",
            'arguments' => array(
                '%%starts%%',
                '%%ter%%',
            ),
        ),
        'COMMCOURSE_QSTART_XFER_GREATER_THAN_QSTART_TER' => array(
            'type' => Message::ERROR,
            'format' => "Quarter Starting Transfer in from Earlier (%%xfer%%) cannot be more than the quarter starting total number of people ever registered in the course (%%ter%%)",
            'arguments' => array(
                '%%xfer%%',
                '%%ter%%',
            ),
        ),
        'COMMCOURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER' => array(
            'type' => Message::ERROR,
            'format' => "Current Standard Starts (%%starts%%) cannot be more than the total number of people ever registered in the course (%%ter%%)",
            'arguments' => array(
                '%%starts%%',
                '%%ter%%',
            ),
        ),
        'COMMCOURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER' => array(
            'type' => Message::ERROR,
            'format' => "Current Transfer in from Earlier (%%xfer%%) cannot be more than the total number of people ever registered in the course (%%ter%%)",
            'arguments' => array(
                '%%xfer%%',
                '%%ter%%',
            ),
        ),
        'COMMCOURSE_CURRENT_TER_LESS_THAN_QSTART_TER' => array(
            'type' => Message::WARNING,
            'format' => "Current Total Ever Registered (%%currentTer%%) is less than Quarter Starting Total Ever Registered (%%quarterStartTer%%). Please verify that this is accurate.",
            'arguments' => array(
                '%%currentTer%%',
                '%%quarterStartTer%%',
            ),
        ),
        'COMMCOURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER' => array(
            'type' => Message::WARNING,
            'format' => "Current Transfer in from Earlier (%%currentXfer%%) is less than the Quarter Starting Transfer in from Earlier (%%quarterStartXfer%%). This should only be possible if some who previously transferred was withdrawn as a registration error.",
            'arguments' => array(
                '%%currentXfer%%',
                '%%quarterStartXfer%%',
            ),
        ),

        // Class List Errors
        'CLASSLIST_GITW_LEAVE_BLANK' => array(
            'type' => Message::ERROR,
            'format' => "If team member is no longer on your team, leave GITW empty.",
            'arguments' => array(),
        ),
        'CLASSLIST_GITW_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "No value provided for GITW.",
            'arguments' => array(),
        ),
        'CLASSLIST_TDO_LEAVE_BLANK' => array(
            'type' => Message::ERROR,
            'format' => "If team member is no longer on your team, leave TDO empty.",
            'arguments' => array(),
        ),
        'CLASSLIST_TDO_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "No value provided for TDO.",
            'arguments' => array(),
        ),
        'CLASSLIST_WKND_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "No value provided for Wknd or X In. One should be %%teamYear%%.",
            'arguments' => array(
                '%%teamYear%%',
            ),
        ),
        'CLASSLIST_WKND_XIN_ONLY_ONE' => array(
            'type' => Message::ERROR,
            'format' => "Only one of Wknd and X In should have a '%%teamYear%%'.",
            'arguments' => array(
                '%%teamYear%%',
            ),
        ),
        'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER' => array(
            'type' => Message::WARNING,
            'format' => "Team member is transferring. Confirm with other center that team member is reported appropriately on their sheet.",
            'arguments' => array(),
        ),
        'CLASSLIST_XFER_COMMENT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Add a comment specifying the date of transfer and the center they transferred to/from.",
            'arguments' => array(),
        ),
        'CLASSLIST_WD_WBO_ONLY_ONE' => array(
            'type' => Message::ERROR,
            'format' => "Both WD and WBO are set. Only one should be set.",
            'arguments' => array(),
        ),
        'CLASSLIST_WD_CTW_ONLY_ONE' => array(
            'type' => Message::ERROR,
            'format' => "Both WD/WBO and CTW are set. CTW should not be set after the team member has withdrawn.",
            'arguments' => array(),
        ),
        'CLASSLIST_WD_DOESNT_MATCH_YEAR' => array(
            'type' => Message::ERROR,
            'format' => "The program year specified for WD doesn't match the team members program year. It should match the value in Wknd or X In",
            'arguments' => array(),
        ),
        'CLASSLIST_WD_COMMENT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Add a comment with the date of withdraw.",
            'arguments' => array(),
        ),
        'CLASSLIST_CTW_COMMENT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Add a comment with the issue/concern.",
            'arguments' => array(),
        ),
        'CLASSLIST_TRAVEL_COMMENT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Either travel must be complete and marked with a Y in the Travel column, or a comment providing a specific promise must be provided",
            'arguments' => array(),
        ),
        'CLASSLIST_ROOM_COMMENT_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "Either rooming must be complete and marked with a Y in the Room column, or a comment providing a specific promise must be provided",
            'arguments' => array(),
        ),
        'CLASSLIST_TRAVEL_COMMENT_REVIEW' => array(
            'type' => Message::WARNING,
            'format' => "Travel is not booked. Make sure the comment provides a specific promise for when travel will be complete.",
            'arguments' => array(),
        ),
        'CLASSLIST_ROOM_COMMENT_REVIEW' => array(
            'type' => Message::WARNING,
            'format' => "Rooming is not booked. Make sure the comment provides a specific promise for when rooming will be complete.",
            'arguments' => array(),
        ),
        'CLASSLIST_TRAVEL_ROOM_CTW_MISSING' => array(
            'type' => Message::ERROR,
            'format' => "If travel and rooming are not complete by 2 weeks before the end of the quarter, mark the team member as a Conversation To Withdraw (CTW).",
            'arguments' => array(),
        ),

        // ImportDocument Errors
        'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND' => array(
            'type' => Message::ERROR,
            'format' => "The %%type%% Quarter Starting Total Registered value you reported (%%reported%%) does not match the number of incoming with registered dates before the quarter's start date (%%calculated%%).",
            'arguments' => array(
                '%%type%%',
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND' => array(
            'type' => Message::ERROR,
            'format' => "The %%type%% Quarter Starting Total Approved value you reported (%%quarterStartApproved%%) does not match the number of incoming with approved dates before the quarter's start date (%%calculated%%).",
            'arguments' => array(
                '%%type%%',
                '%%quarterStartApproved%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND' => array(
            'type' => Message::WARNING,
            'format' => "The T1 Quarter Starting Total Registered totals reported (%%reported%%) do not match the number of incoming with registered dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND' => array(
            'type' => Message::WARNING,
            'format' => "The T1 Quarter Starting Total Approved totals reported (%%reported%%) do not match the number of incoming with approved dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND' => array(
            'type' => Message::WARNING,
            'format' => "The T2 Quarter Starting Total Registered totals reported (%%reported%%) do not match the number of incoming with registered dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND' => array(
            'type' => Message::WARNING,
            'format' => "The T2 Quarter Starting Total Approved totals reported (%%reported%%) do not match the number of incoming with approved dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),

        'IMPORTDOC_CAP_ACTUAL_INCORRECT' => array(
            'type' => Message::ERROR,
            'format' => "The CAP actual that you reported this week (%%reported%%) does not match the net number of CAP registrations this quarter (%%calculated%%).",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_CPC_ACTUAL_INCORRECT' => array(
            'type' => Message::ERROR,
            'format' => "The CPC actual that you reported this week (%%reported%%) does not match the net number of CPC registrations this quarter (%%calculated%%).",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_T1X_ACTUAL_INCORRECT' => array(
            'type' => Message::WARNING,
            'format' => "The T1X actual that you reported this week (%%reported%%) does not match the net number of T1 incoming approved this quarter (%%calculated%%). If the sheet does flag this in purple, the quarter starting totals are likely inaccurate.",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_T2X_ACTUAL_INCORRECT' => array(
            'type' => Message::WARNING,
            'format' => "The T2X actual that you reported this week (%%reported%%) does not match the net number of T2 incoming approved this quarter (%%calculated%%). If the sheet does flag this in purple, the quarter starting totals are likely inaccurate.",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_GITW_ACTUAL_INCORRECT' => array(
            'type' => Message::ERROR,
            'format' => "The GITW actual that you reported this week (%%reported%%%) does not match the percentage of team members reported as effective (%%calculated%%%).",
            'arguments' => array(
                '%%reported%%',
                '%%calculated%%',
            ),
        ),
        'IMPORTDOC_SPREADSHEET_VERSION_MISMATCH' => array(
            'type' => Message::ERROR,
            'format' => "Spreadsheet version (%%reported%%) doesn't match expected version (%%expected%%).",
            'arguments' => array(
                '%%reported%%',
                '%%expected%%',
            ),
        ),
        'IMPORTDOC_SPREADSHEET_DATE_MISMATCH' => array(
            'type' => Message::ERROR,
            'format' => "Spreadsheet date (%%reported%%) doesn't match expected date (%%expected%%).",
            'arguments' => array(
                '%%reported%%',
                '%%expected%%',
            ),
        ),
        'IMPORTDOC_SPREADSHEET_DATE_MISMATCH_LAST_WEEK' => array(
            'type' => Message::ERROR,
            'format' => "Spreadsheet date (%%reported%%) doesn't match expected date (%%expected%%). If this is the last week of the quarter and you are reporting preliminary results, use Friday's date.",
            'arguments' => array(
                '%%reported%%',
                '%%expected%%',
            ),
        ),

        'IMPORTDOC_CENTER_NOT_FOUND' => array(
            'type' => Message::ERROR,
            'format' => "Could not find center '%%centerName%%'. The name may not match our list or this sheet may be an invalid/corrupt. Make sure the center name is the same as one in this list: %%centerList%%.",
            'arguments' => array(
                '%%centerName%%',
                '%%centerList%%',
            ),
        ),
        'IMPORTDOC_CENTER_INACTIVE' => array(
            'type' => Message::ERROR,
            'format' => "Center '%%centerName%%' is marked as inactive. Please have an administrator activate this center if they are now an active team.",
            'arguments' => array(
                '%%centerName%%',
            ),
        ),
        'IMPORTDOC_DATE_FORMAT_INCORRECT' => array(
            'type' => Message::ERROR,
            'format' => "Reporting date format was incorrect, '%%reportingDate%%'. Please input date explicitly (i.e. 'May 15, 2015').",
            'arguments' => array(
                '%%reportingDate%%',
            ),
        ),
        'IMPORTDOC_DATE_NOT_FOUND' => array(
            'type' => Message::ERROR,
            'format' => "Could not find reporting date. Got '%%reportingDate%%'. The date format may be incorrect or this may be an invalid/corrupt sheet. Please input date explicitly (i.e. 'May 15, 2015').",
            'arguments' => array(
                '%%reportingDate%%',
            ),
        ),
        'IMPORTDOC_VERSION_FORMAT_INCORRECT' => array(
            'type' => Message::ERROR,
            'format' => "Version '%%version%%' is in an incorrect format. Sheet may be invalid/corrupt.",
            'arguments' => array(
                '%%version%%',
            ),
        ),
        'IMPORTDOC_QUARTER_NOT_FOUND' => array(
            'type' => Message::ERROR,
            'format' => "Could not find quarter with date '%%reportingDate%%'. This may be an invalid/corrupt sheet",
            'arguments' => array(
                '%%reportingDate%%',
            ),
        ),
        'IMPORTDOC_STATS_REPORT_LOCKED' => array(
            'type' => Message::ERROR,
            'format' => "Stats report for %%centerName%% on %%reportingDate%% is locked and cannot be overwritten.",
            'arguments' => array(
                '%%centerName%%',
                '%%reportingDate%%',
            ),
        ),

        // CenterStats Importer Errors
        'CENTERSTATS_WEEK_DATE_FORMAT_INVALID' => array(
            'type' => Message::ERROR,
            'format' => "Week end date in column %%col%% is not in the correct format. The sheet may be corrupt.",
            'arguments' => array(
                '%%col%%',
            ),
        ),

        // Comm Course Importer Errors
        'COMMCOURSE_START_DATE_FORMAT_INVALID' => array(
            'type' => Message::ERROR,
            'format' => "Start date format is invalid for %%type%% course.",
            'arguments' => array(
                '%%type%%',
            ),
        ),
        'COMMCOURSE_START_DATE_FORMAT_UNREADABLE' => array(
            'type' => Message::ERROR,
            'format' => "Unable to determine start date for %%type%% course due to invalid date format. Validation may be skipped. Check manually.",
            'arguments' => array(
                '%%type%%',
            ),
        ),

        // Contact Info Importer Errors
        'CONTACTINFO_NO_NAME' => array(
            'type' => Message::WARNING,
            'format' => "No name provided for %%accountability%%.",
            'arguments' => array(
                '%%accountability%%',
            ),
        ),
        'CONTACTINFO_SLASHES_FOUND' => array(
            'type' => Message::ERROR,
            'format' => "Please provide name like 'Jane D' with a space, not a '/' separating the first name and last initial.",
            'arguments' => array(),
        ),


        // '' => array(
        //     'type' => Message::ERROR,
        //     'format' => "",
        //     'arguments' => array(
        //     ),
        // ),
    );

    protected $section = '';

    public static function create($section)
    {
        $me = new static();
        $me->section = $section;
        return $me;
    }

    public function addMessage($messageId, $offset)
    {

        $message = static::$messageList[$messageId];
        $arguments = array_slice(func_get_args(), 2);

        $result = array(
            'type'    => $this->getMessageTypeString($message['type']),
            'section' => $this->section,
            'message' => $this->getMessageFromFormat($messageId, $message['format'], $message['arguments'], $arguments),
        );

        if ($offset !== false) {
            $result['offset'] = $offset;
            $result['offsetType'] = $this->getOffsetType($offset);
        }

        return $result;
    }

    protected function getMessageFromFormat($messageId, $format, $argumentNames, $arguments = array())
    {
        if (count($argumentNames) != count($arguments)) {
            throw new \Exception("Argument name and value counts do not match for '$messageId'");
        } else if (count($argumentNames) == 0) {
            return $format;
        }

        $message = $format;

        for ($i = 0; $i < count($argumentNames); $i++) {
            $name = $argumentNames[$i];
            $value = $arguments[$i];

            $message = str_replace($name, $value, $message);
        }

        return $message;
    }

    protected function getOffsetType($offset)
    {
        if (preg_match('/^[a-z]+$/i', $offset)) {
            return 'column';
        } else if (preg_match('/^[\d]+$/', $offset)) {
            return 'row';
        } else if (preg_match('/^[a-z]+[\d]+$/i', $offset)) {
            return 'cell';
        } else {
            return 'offset';
        }
    }

    protected function getMessageTypeString($type)
    {
        switch ($type) {
            case static::EMERGENCY:
                return 'emergency';
            case static::ALERT:
                return 'alert';
            case static::CRITICAL:
                return 'critical';
            case static::ERROR:
                return 'error';
            case static::WARNING:
                return 'warning';
            case static::NOTICE:
                return 'notice';
            case static::INFO:
                return 'info';
            case static::DEBUG:
            default:
                return 'debug';
        }
    }
}

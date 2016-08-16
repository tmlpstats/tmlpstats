<?php
namespace TmlpStats;

class Message
{
    const EMERGENCY = 1;
    const ALERT = 2;
    const CRITICAL = 3;
    const ERROR = 4;
    const WARNING = 5;
    const NOTICE = 6;
    const INFO = 7;
    const DEBUG = 8;

    // Message Definitions
    protected $messageList = [
        // General Errors
        'INVALID_VALUE' => [
            'type' => Message::ERROR,
            'format' => "Incorrect value provided for %%display_name%% ('%%value%%').",
            'arguments' => [
                '%%display_name%%',
                '%%value%%',
            ],
        ],
        'IMPORT_TAB_FAILED' => [
            'type' => Message::ERROR,
            'format' => 'Unable to import tab.',
            'arguments' => [],
        ],
        'EXCEPTION_LOADING_ENTRY' => [
            'type' => Message::ERROR,
            'format' => 'There was an error processing tab: %%message%%.',
            'arguments' => [
                '%%message%%',
            ],
        ],

        // TMLP Registration Validator Errors
        'TMLPREG_MULTIPLE_WEEKENDREG' => [
            'type' => Message::ERROR,
            'format' => "Weekend Reg section contains multiple %%incomingTeamYear%%'s. Only one should be provided",
            'arguments' => [
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_WD_MISSING' => [
            'type' => Message::ERROR,
            'format' => "Withdraw date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_WD_DATE_MISSING' => [
            'type' => Message::ERROR,
            'format' => "No withdraw date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_WD_DOESNT_MATCH_INCOMING_YEAR' => [
            'type' => Message::ERROR,
            'format' => "The program year specified for WD doesn't match the incoming program year. It should match the value in the Weekend Reg columns",
            'arguments' => [],
        ],
        'TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR' => [
            'type' => Message::ERROR,
            'format' => "If person has withdrawn, only column '%%offset%%' should contain a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPR_MISSING' => [
            'type' => Message::ERROR,
            'format' => "Approved date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPR_DATE_MISSING' => [
            'type' => Message::ERROR,
            'format' => "No approved date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR' => [
            'type' => Message::ERROR,
            'format' => "If person is approved, only column '%%offset%%' should contain a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPR_MISSING_APPIN_DATE' => [
            'type' => Message::ERROR,
            'format' => 'No app in date provided',
            'arguments' => [],
        ],
        'TMLPREG_APPR_MISSING_APPOUT_DATE' => [
            'type' => Message::ERROR,
            'format' => 'No app out date provided',
            'arguments' => [],
        ],
        'TMLPREG_APPIN_MISSING' => [
            'type' => Message::ERROR,
            'format' => "App in date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPIN_DATE_MISSING' => [
            'type' => Message::ERROR,
            'format' => "No app in date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPIN_ONLY_ONE_YEAR_INDICATOR' => [
            'type' => Message::ERROR,
            'format' => "If person's application is in, only column '%%offset%%' should contain a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPIN_MISSING_APPOUT_DATE' => [
            'type' => Message::ERROR,
            'format' => 'No app out date provided',
            'arguments' => [],
        ],
        'TMLPREG_APPOUT_MISSING' => [
            'type' => Message::ERROR,
            'format' => "App out date was provided, but '%%offset%%' column does not contain a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_APPOUT_DATE_MISSING' => [
            'type' => Message::ERROR,
            'format' => "No app out date was provided, but '%%offset%%' column contains a %%incomingTeamYear%%",
            'arguments' => [
                '%%offset%%',
                '%%incomingTeamYear%%',
            ],
        ],
        'TMLPREG_NO_COMMITTED_TEAM_MEMBER' => [
            'type' => Message::ERROR,
            'format' => 'No committed team member provided',
            'arguments' => [],
        ],
        'TMLPREG_COMMITTED_TEAM_MEMBER_NO_MATCHING_TEAM_MEMBER' => [
            'type' => Message::WARNING,
            'format' => 'Unable to find a team member with the name %%name%% on the Class List for committed team member. Please make sure the first name and last initial match the team member exactly.',
            'arguments' => [
                '%%name%%',
            ],
        ],
        'TMLPREG_WD_DATE_BEFORE_REG_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Withdraw date is before registration date',
            'arguments' => [],
        ],
        'TMLPREG_WD_DATE_BEFORE_APPR_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Withdraw date is before approval date',
            'arguments' => [],
        ],
        'TMLPREG_WD_DATE_BEFORE_APPIN_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Withdraw date is before app in date',
            'arguments' => [],
        ],
        'TMLPREG_WD_DATE_BEFORE_APPOUT_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Withdraw date is before app out date',
            'arguments' => [],
        ],
        'TMLPREG_APPR_DATE_BEFORE_REG_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Approval date is before registration date',
            'arguments' => [],
        ],
        'TMLPREG_APPR_DATE_BEFORE_APPIN_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Approval date is before app in date',
            'arguments' => [],
        ],
        'TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Approval date is before app out date',
            'arguments' => [],
        ],
        'TMLPREG_APPIN_DATE_BEFORE_REG_DATE' => [
            'type' => Message::ERROR,
            'format' => 'App in date is before registration date',
            'arguments' => [],
        ],
        'TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE' => [
            'type' => Message::ERROR,
            'format' => 'App in date is before app out date',
            'arguments' => [],
        ],
        'TMLPREG_APPOUT_DATE_BEFORE_REG_DATE' => [
            'type' => Message::ERROR,
            'format' => 'App out date is before registration date',
            'arguments' => [],
        ],
        'TMLPREG_BEF_REG_DATE_NOT_BEFORE_WEEKEND' => [
            'type' => Message::ERROR,
            'format' => "Registration is not before the quarter's start date (%%date%%) but has a %%value%% in Bef column",
            'arguments' => [
                '%%date%%',
                '%%value%%',
            ],
        ],
        'TMLPREG_DUR_REG_DATE_NOT_DURING_WEEKEND' => [
            'type' => Message::ERROR,
            'format' => 'Registration date is not during quarter start weekend (%%date%%) but has a %%value%% in Dur column',
            'arguments' => [
                '%%date%%',
                '%%value%%',
            ],
        ],
        'TMLPREG_AFT_REG_DATE_NOT_AFTER_WEEKEND' => [
            'type' => Message::ERROR,
            'format' => 'Registration date is not after quarter start date (%%date%%) but has a %%value%% in Aft column',
            'arguments' => [
                '%%date%%',
                '%%value%%',
            ],
        ],
        'TMLPREG_APPOUT_LATE' => [
            'type' => Message::WARNING,
            'format' => 'Application was not sent to applicant within %%daysSince%% days of registration.',
            'arguments' => [
                '%%daysSince%%',
            ],
        ],
        'TMLPREG_APPIN_LATE' => [
            'type' => Message::WARNING,
            'format' => 'Application not returned within %%daysSince%% days since sending application out. Application is not in integrity with design of application process.',
            'arguments' => [
                '%%daysSince%%',
            ],
        ],
        'TMLPREG_APPR_LATE' => [
            'type' => Message::WARNING,
            'format' => 'Application not approved within %%daysSince%% days since sending application out.',
            'arguments' => [
                '%%daysSince%%',
            ],
        ],
        'TMLPREG_REG_DATE_IN_FUTURE' => [
            'type' => Message::ERROR,
            'format' => 'Registration date is in the future. Please check date.',
            'arguments' => [],
        ],
        'TMLPREG_WD_DATE_IN_FUTURE' => [
            'type' => Message::ERROR,
            'format' => 'Withdraw date is in the future. Please check date.',
            'arguments' => [],
        ],
        'TMLPREG_APPR_DATE_IN_FUTURE' => [
            'type' => Message::ERROR,
            'format' => 'Approve date is in the future. Please check date.',
            'arguments' => [],
        ],
        'TMLPREG_APPIN_DATE_IN_FUTURE' => [
            'type' => Message::ERROR,
            'format' => 'Application In date is in the future. Please check date.',
            'arguments' => [],
        ],
        'TMLPREG_APPOUT_DATE_IN_FUTURE' => [
            'type' => Message::ERROR,
            'format' => 'Application Out date is in the future. Please check date.',
            'arguments' => [],
        ],
        'TMLPREG_COMMENT_MISSING_FUTURE_WEEKEND' => [
            'type' => Message::ERROR,
            'format' => 'No comment provided specifying incoming weekend for future registration',
            'arguments' => [],
        ],
        'TMLPREG_TRAVEL_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Either travel must be complete and marked with a Y in the Travel column, or a comment with a specific promise must be provided',
            'arguments' => [],
        ],
        'TMLPREG_ROOM_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Either rooming must be complete and marked with a Y in the Room column, or a comment with a specific promise must be provided',
            'arguments' => [],
        ],
        'TMLPREG_TRAVEL_COMMENT_REVIEW' => [
            'type' => Message::WARNING,
            'format' => 'Travel is not booked. Make sure the comment provides a specific promise for when travel will be complete.',
            'arguments' => [],
        ],
        'TMLPREG_ROOM_COMMENT_REVIEW' => [
            'type' => Message::WARNING,
            'format' => 'Rooming is not booked. Make sure the comment provides a specific promise for when rooming will be complete.',
            'arguments' => [],
        ],
        'TMLPREG_DUPLICATE_NAME' => [
            'type' => Message::ERROR,
            'format' => "There are multiple registrations with the name '%%firstName%% %%lastName%%'. You may have accidentally added them twice. If you have 2 people with the same first name and last initial, please provide an additional letter of their last name so we can tell them apart. Make sure to use the same spelling if you reference them on other tabs.",
            'arguments' => [
                '%%firstName%%',
                '%%lastName%%',
            ],
        ],

        // TMLP Course Info Errors
        'TMLPCOURSE_QSTART_TER_LESS_THAN_QSTART_APPROVED' => [
            'type' => Message::ERROR,
            'format' => 'Quarter Starting Total Registered is less than Quarter Starting Total Approved. Starting Registered should include Approved applications.',
            'arguments' => [],
        ],

        // Communication Course Info Errors
        'COMMCOURSE_COMPLETED_SS_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Course has completed but is missing Standard Starts Completed',
            'arguments' => [],
        ],
        'COMMCOURSE_POTENTIALS_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Course has completed but is missing Potentials',
            'arguments' => [],
        ],
        'COMMCOURSE_REGISTRATIONS_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Course has completed but is missing Registrations',
            'arguments' => [],
        ],
        'COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE' => [
            'type' => Message::ERROR,
            'format' => 'Course Completion stats provided, but course has not completed.',
            'arguments' => [],
        ],
        'COMMCOURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS' => [
            'type' => Message::WARNING,
            'format' => 'Completed Standard Starts is %%difference%% less than the course starting standard starts. Confirm with your regional statistician that %%difference%% people did withdraw during the course.',
            'arguments' => [
                '%%difference%%',
            ],
        ],
        'COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS' => [
            'type' => Message::ERROR,
            'format' => 'More people completed the course than there were that started. Make sure Current Standard Starts matches the number of people that started the course, and Completed Standard Starts matches the number of people that completed the course.',
            'arguments' => [],
        ],
        'COMMCOURSE_COMPLETED_REGISTRATIONS_GREATER_THAN_POTENTIALS' => [
            'type' => Message::ERROR,
            'format' => 'Registrations (%%registrations%%) cannot be greater than the number of potentials for the course (%%potentials%%). Please confirm what the correct values are with the course supervisor and/or your program manager.',
            'arguments' => [
                '%%potentials%%',
                '%%registrations%%',
            ],
        ],
        'COMMCOURSE_COURSE_DATE_BEFORE_QUARTER' => [
            'type' => Message::ERROR,
            'format' => 'Course occured before quarter started',
            'arguments' => [],
        ],
        'COMMCOURSE_QSTART_SS_GREATER_THAN_QSTART_TER' => [
            'type' => Message::ERROR,
            'format' => 'Quarter Starting Standard Starts (%%starts%%) cannot be more than the quarter starting total number of people ever registered in the course (%%ter%%)',
            'arguments' => [
                '%%starts%%',
                '%%ter%%',
            ],
        ],
        'COMMCOURSE_QSTART_XFER_GREATER_THAN_QSTART_TER' => [
            'type' => Message::ERROR,
            'format' => 'Quarter Starting Transfer in from Earlier (%%xfer%%) cannot be more than the quarter starting total number of people ever registered in the course (%%ter%%)',
            'arguments' => [
                '%%xfer%%',
                '%%ter%%',
            ],
        ],
        'COMMCOURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER' => [
            'type' => Message::ERROR,
            'format' => 'Current Standard Starts (%%starts%%) cannot be more than the total number of people ever registered in the course (%%ter%%)',
            'arguments' => [
                '%%starts%%',
                '%%ter%%',
            ],
        ],
        'COMMCOURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER' => [
            'type' => Message::ERROR,
            'format' => 'Current Transfer in from Earlier (%%xfer%%) cannot be more than the total number of people ever registered in the course (%%ter%%)',
            'arguments' => [
                '%%xfer%%',
                '%%ter%%',
            ],
        ],
        'COMMCOURSE_CURRENT_TER_LESS_THAN_QSTART_TER' => [
            'type' => Message::WARNING,
            'format' => 'Current Total Ever Registered (%%currentTer%%) is less than Quarter Starting Total Ever Registered (%%quarterStartTer%%). Please verify that this is accurate.',
            'arguments' => [
                '%%currentTer%%',
                '%%quarterStartTer%%',
            ],
        ],
        'COMMCOURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER' => [
            'type' => Message::WARNING,
            'format' => 'Current Transfer in from Earlier (%%currentXfer%%) is less than the Quarter Starting Transfer in from Earlier (%%quarterStartXfer%%). This should only be possible if some who previously transferred was withdrawn as a registration error.',
            'arguments' => [
                '%%currentXfer%%',
                '%%quarterStartXfer%%',
            ],
        ],
        'COMMCOURSE_GUESTS_INVITED_MISSING' => [
            'type' => Message::ERROR,
            'format' => "Course has completed but the guest game's number of people invited is missing.",
            'arguments' => [],
        ],
        'COMMCOURSE_GUESTS_CONFIRMED_MISSING' => [
            'type' => Message::ERROR,
            'format' => "Course has completed but the guest game's number of people confirmed is missing.",
            'arguments' => [],
        ],
        'COMMCOURSE_GUESTS_ATTENDED_MISSING' => [
            'type' => Message::ERROR,
            'format' => "Course has completed but the guest game's number of people who attended is missing.",
            'arguments' => [],
        ],
        'COMMCOURSE_GUESTS_ATTENDED_PROVIDED_BEFORE_COURSE' => [
            'type' => Message::ERROR,
            'format' => 'Course has not completed yet, but the guest game shows that people have already attended.',
            'arguments' => [],
        ],

        // Class List Errors
        'CLASSLIST_GITW_LEAVE_BLANK' => [
            'type' => Message::ERROR,
            'format' => 'If team member is no longer on your team, leave GITW empty.',
            'arguments' => [],
        ],
        'CLASSLIST_GITW_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'No value provided for GITW.',
            'arguments' => [],
        ],
        'CLASSLIST_TDO_LEAVE_BLANK' => [
            'type' => Message::ERROR,
            'format' => 'If team member is no longer on your team, leave TDO empty.',
            'arguments' => [],
        ],
        'CLASSLIST_TDO_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'No value provided for TDO.',
            'arguments' => [],
        ],
        'CLASSLIST_WKND_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'No value provided for Wknd, X In or Rereg. One should be %%teamYear%%.',
            'arguments' => [
                '%%teamYear%%',
            ],
        ],
        'CLASSLIST_WKND_XIN_REREG_ONLY_ONE' => [
            'type' => Message::ERROR,
            'format' => "Only one of Wknd, X In and Rereg should have a '%%teamYear%%'.",
            'arguments' => [
                '%%teamYear%%',
            ],
        ],
        'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER' => [
            'type' => Message::WARNING,
            'format' => 'Team member is transferring. Confirm with other center that team member is reported appropriately on their sheet.',
            'arguments' => [],
        ],
        'CLASSLIST_XFER_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Add a comment specifying the date of transfer and the center they transferred to/from.',
            'arguments' => [],
        ],
        'CLASSLIST_WD_WBO_ONLY_ONE' => [
            'type' => Message::ERROR,
            'format' => 'Both WD and WBO are set. Only one should be set.',
            'arguments' => [],
        ],
        'CLASSLIST_WD_CTW_ONLY_ONE' => [
            'type' => Message::ERROR,
            'format' => 'Both WD/WBO and CTW are set. CTW should not be set after the team member has withdrawn.',
            'arguments' => [],
        ],
        'CLASSLIST_WD_DOESNT_MATCH_YEAR' => [
            'type' => Message::ERROR,
            'format' => "The program year specified for WD doesn't match the team members program year. It should match the value in Wknd or X In",
            'arguments' => [],
        ],
        'CLASSLIST_WD_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Add a comment with the date of withdraw.',
            'arguments' => [],
        ],
        'CLASSLIST_CTW_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Add a comment with the issue/concern.',
            'arguments' => [],
        ],
        'CLASSLIST_TRAVEL_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Either travel must be complete and marked with a Y in the Travel column, or a comment providing a specific promise must be provided',
            'arguments' => [],
        ],
        'CLASSLIST_ROOM_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Either rooming must be complete and marked with a Y in the Room column, or a comment providing a specific promise must be provided',
            'arguments' => [],
        ],
        'CLASSLIST_TRAVEL_COMMENT_REVIEW' => [
            'type' => Message::WARNING,
            'format' => 'Travel is not booked. Make sure the comment provides a specific promise for when travel will be complete.',
            'arguments' => [],
        ],
        'CLASSLIST_ROOM_COMMENT_REVIEW' => [
            'type' => Message::WARNING,
            'format' => 'Rooming is not booked. Make sure the comment provides a specific promise for when rooming will be complete.',
            'arguments' => [],
        ],
        'CLASSLIST_DUPLICATE_TEAM_MEMBER' => [
            'type' => Message::ERROR,
            'format' => "There are multiple team members with the name '%%firstName%% %%lastName%%'. You may have accidentally added them twice. If you have 2 people with the same first name and last initial, please provide an additional letter of their last name so we can tell them apart. Make sure to use the same spelling if you reference them on other tabs.",
            'arguments' => [
                '%%firstName%%',
                '%%lastName%%',
            ],
        ],

        // ImportDocument Errors
        'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND' => [
            'type' => Message::ERROR,
            'format' => "The %%type%% Quarter Starting Total Registered value you reported (%%reported%%) does not match the number of incoming with registered dates before the quarter's start date (%%calculated%%).",
            'arguments' => [
                '%%type%%',
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND' => [
            'type' => Message::ERROR,
            'format' => "The %%type%% Quarter Starting Total Approved value you reported (%%quarterStartApproved%%) does not match the number of incoming with approved dates before the quarter's start date (%%calculated%%).",
            'arguments' => [
                '%%type%%',
                '%%quarterStartApproved%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND' => [
            'type' => Message::WARNING,
            'format' => "The T1 Quarter Starting Total Registered totals reported (%%reported%%) do not match the number of incoming with registered dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND' => [
            'type' => Message::WARNING,
            'format' => "The T1 Quarter Starting Total Approved totals reported (%%reported%%) do not match the number of incoming with approved dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND' => [
            'type' => Message::WARNING,
            'format' => "The T2 Quarter Starting Total Registered totals reported (%%reported%%) do not match the number of incoming with registered dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND' => [
            'type' => Message::WARNING,
            'format' => "The T2 Quarter Starting Total Approved totals reported (%%reported%%) do not match the number of incoming with approved dates before the quarter's start date (%%calculated%%). Did someone transfer from another center?",
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],

        'IMPORTDOC_CAP_ACTUAL_INCORRECT' => [
            'type' => Message::ERROR,
            'format' => 'The CAP actual that you reported this week (%%reported%%) does not match the net number of CAP registrations this quarter (%%calculated%%).',
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_CPC_ACTUAL_INCORRECT' => [
            'type' => Message::ERROR,
            'format' => 'The CPC actual that you reported this week (%%reported%%) does not match the net number of CPC registrations this quarter (%%calculated%%).',
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_T1X_ACTUAL_INCORRECT' => [
            'type' => Message::ERROR,
            'format' => 'The T1X actual that you reported this week (%%reported%%) does not match the net number of T1 incoming approved this quarter (%%calculated%%). If the sheet does flag this in purple, the quarter starting totals are likely inaccurate.',
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_T2X_ACTUAL_INCORRECT' => [
            'type' => Message::ERROR,
            'format' => 'The T2X actual that you reported this week (%%reported%%) does not match the net number of T2 incoming approved this quarter (%%calculated%%). If the sheet does flag this in purple, the quarter starting totals are likely inaccurate.',
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_GITW_ACTUAL_INCORRECT' => [
            'type' => Message::ERROR,
            'format' => 'The GITW actual that you reported this week (%%reported%%%) does not match the percentage of team members reported as effective (%%calculated%%%).',
            'arguments' => [
                '%%reported%%',
                '%%calculated%%',
            ],
        ],
        'IMPORTDOC_SPREADSHEET_VERSION_MISMATCH' => [
            'type' => Message::ERROR,
            'format' => "Spreadsheet version (%%reported%%) doesn't match expected version (%%expected%%).",
            'arguments' => [
                '%%reported%%',
                '%%expected%%',
            ],
        ],
        'IMPORTDOC_SPREADSHEET_DATE_MISMATCH' => [
            'type' => Message::ERROR,
            'format' => "Spreadsheet date (%%reported%%) doesn't match expected date (%%expected%%).",
            'arguments' => [
                '%%reported%%',
                '%%expected%%',
            ],
        ],
        'IMPORTDOC_SPREADSHEET_DATE_MISMATCH_LAST_WEEK' => [
            'type' => Message::ERROR,
            'format' => "Spreadsheet date (%%reported%%) doesn't match expected date (%%expected%%). If this is the last week of the quarter and you are reporting preliminary results, use Friday's date.",
            'arguments' => [
                '%%reported%%',
                '%%expected%%',
            ],
        ],

        'IMPORTDOC_CENTER_NOT_FOUND' => [
            'type' => Message::ERROR,
            'format' => "Could not find center '%%centerName%%'. The name may not match our list or this sheet may be an invalid/corrupt. Make sure the center name is the same as one in this list: %%centerList%%.",
            'arguments' => [
                '%%centerName%%',
                '%%centerList%%',
            ],
        ],
        'IMPORTDOC_CENTER_INACTIVE' => [
            'type' => Message::ERROR,
            'format' => "Center '%%centerName%%' is marked as inactive. Please have an administrator activate this center if they are now an active team.",
            'arguments' => [
                '%%centerName%%',
            ],
        ],
        'IMPORTDOC_DATE_FORMAT_INCORRECT' => [
            'type' => Message::ERROR,
            'format' => "Reporting date format was incorrect, '%%reportingDate%%'. Please input date explicitly (i.e. 'May 15, 2015').",
            'arguments' => [
                '%%reportingDate%%',
            ],
        ],
        'IMPORTDOC_DATE_NOT_FOUND' => [
            'type' => Message::ERROR,
            'format' => "Could not find reporting date. Got '%%reportingDate%%'. The date format may be incorrect or this may be an invalid/corrupt sheet. Please input date explicitly (i.e. 'May 15, 2015').",
            'arguments' => [
                '%%reportingDate%%',
            ],
        ],
        'IMPORTDOC_DATE_NOT_FRIDAY' => [
            'type' => Message::ERROR,
            'format' => "The date provided '%%reportingDate%%' is not on a Friday. Reports are dated on the Friday the statistics closed even if they are not submitted on Friday. Please correct the date and retry.",
            'arguments' => [
                '%%reportingDate%%',
            ],
        ],
        'IMPORTDOC_VERSION_FORMAT_INCORRECT' => [
            'type' => Message::ERROR,
            'format' => "Version '%%version%%' is in an incorrect format. Sheet may be invalid/corrupt.",
            'arguments' => [
                '%%version%%',
            ],
        ],
        'IMPORTDOC_QUARTER_NOT_FOUND' => [
            'type' => Message::ERROR,
            'format' => "Could not find quarter with date '%%reportingDate%%'. This may be an invalid/corrupt sheet",
            'arguments' => [
                '%%reportingDate%%',
            ],
        ],
        'IMPORTDOC_STATS_REPORT_LOCKED' => [
            'type' => Message::ERROR,
            'format' => 'Stats report for %%centerName%% on %%reportingDate%% is locked and cannot be overwritten.',
            'arguments' => [
                '%%centerName%%',
                '%%reportingDate%%',
            ],
        ],

        // CenterStats Importer Errors
        'CENTERSTATS_WEEK_DATE_FORMAT_INVALID' => [
            'type' => Message::ERROR,
            'format' => 'Week end date in column %%col%% is not in the correct format. The sheet may be corrupt.',
            'arguments' => [
                '%%col%%',
            ],
        ],

        // Comm Course Importer Errors
        'COMMCOURSE_START_DATE_FORMAT_INVALID' => [
            'type' => Message::ERROR,
            'format' => 'Start date format is invalid for %%type%% course.',
            'arguments' => [
                '%%type%%',
            ],
        ],
        'COMMCOURSE_START_DATE_FORMAT_UNREADABLE' => [
            'type' => Message::ERROR,
            'format' => 'Unable to determine start date for %%type%% course due to invalid date format. Validation may be skipped. Check manually.',
            'arguments' => [
                '%%type%%',
            ],
        ],

        // Contact Info Importer Errors
        'CONTACTINFO_NO_NAME' => [
            'type' => Message::ERROR,
            'format' => 'No name provided for %%accountability%%. If your team does not have someone for this role, please put N/A in the name column.',
            'arguments' => [
                '%%accountability%%',
            ],
        ],
        'CONTACTINFO_SLASHES_FOUND' => [
            'type' => Message::ERROR,
            'format' => "Please provide name like 'Jane D' with a space, not a '/' separating the first name and last initial.",
            'arguments' => [],
        ],
        'CONTACTINFO_NO_MATCHING_TEAM_MEMBER' => [
            'type' => Message::WARNING,
            'format' => 'Unable to find a team member with the name %%name%% on the Class List for %%accountability%%. Please make sure the first name and last initial match the team member exactly.',
            'arguments' => [
                '%%name%%',
                '%%accountability%%',
            ],
        ],
        'CONTACTINFO_BOUNCED_EMAIL' => [
            'type' => Message::ERROR,
            'format' => 'The email provided for %%accountability%% (%%email%%) is not reachable. Please correct it.',
            'arguments' => [
                '%%accountability%%',
                '%%email%%',
            ],
        ],

        // Team Application
        'TEAMAPP_WD_CODE_MISSING' => [
            'type' => Message::ERROR,
            'format' => "Withdraw date was provided, but no reason was provided.",
            'arguments' => [],
        ],
        'TEAMAPP_WD_DATE_MISSING' => [
            'type' => Message::ERROR,
            'format' => "Withdraw reason was provided, but no withdraw date.",
            'arguments' => [],
        ],
        'TEAMAPP_APPR_MISSING_APPIN_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Approval date provided, but App In date is missing.',
            'arguments' => [],
        ],
        'TEAMAPP_APPR_MISSING_APPOUT_DATE' => [
            'type' => Message::ERROR,
            'format' => 'Approval date provided, but App Out date is missing.',
            'arguments' => [],
        ],
        'TEAMAPP_APPIN_MISSING_APPOUT_DATE' => [
            'type' => Message::ERROR,
            'format' => 'App In date provided, but App Out date is missing.',
            'arguments' => [],
        ],
        // TODO: rewrite the message text with a more accurate error
        'TEAMAPP_TRAVEL_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Either travel must be complete and marked with a Y in the Travel column, or a comment with a specific promise must be provided',
            'arguments' => [],
        ],
        // TODO: rewrite the message text with a more accurate error
        'TEAMAPP_ROOM_COMMENT_MISSING' => [
            'type' => Message::ERROR,
            'format' => 'Either rooming must be complete and marked with a Y in the Room column, or a comment with a specific promise must be provided',
            'arguments' => [],
        ],
        // TODO: rewrite the message text with a more accurate error
        'TEAMAPP_TRAVEL_COMMENT_REVIEW' => [
            'type' => Message::WARNING,
            'format' => 'Travel is not booked. Make sure the comment provides a specific promise for when travel will be complete.',
            'arguments' => [],
        ],
        // TODO: rewrite the message text with a more accurate error
        'TEAMAPP_ROOM_COMMENT_REVIEW' => [
            'type' => Message::WARNING,
            'format' => 'Rooming is not booked. Make sure the comment provides a specific promise for when rooming will be complete.',
            'arguments' => [],
        ],
        'TEAMAPP_REVIEWER_TEAM1' => [
            'type' => Message::ERROR,
            'format' => 'Only Team 2 can be reviewers. Please check that the team year and reviewer statuses are correct.',
            'arguments' => [],
        ],
    ];

    protected $section = '';

    public static function create($section)
    {
        $me = new static();
        $me->section = $section;

        return $me;
    }

    public function addMessage($messageId, $offset)
    {
        $message = $this->messageList[$messageId];
        $arguments = array_slice(func_get_args(), 2);

        $result = [
            'id' => $messageId,
            'type' => $this->getMessageTypeString($message['type']),
            'section' => $this->section,
            'message' => $this->getMessageFromFormat($messageId, $message['format'], $message['arguments'], $arguments),
        ];

        if ($offset !== null) {
            $result['offset'] = $offset;
            $result['reference'] = $offset;
            $result['offsetType'] = $this->getOffsetType($offset);
        }

        return $result;
    }

    protected function getMessageFromFormat($messageId, $format, $argumentNames, $arguments = [])
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

Hi {{ $user }},<br/>
<br/>
Thank you for submitting stats for team {{ $centerName }}. We received them on {{ $time }} your local time. Please find the submitted sheet attached.<br/>
<br/>
@if (isset($sheet['errors']) && $sheet['errors'])
    Your sheet contained errors. Please review the errors below and correct them for your report next week. If you have any questions please reach out to your regional statistician.<br/>
    <br/>
    Your stats are incomplete this week. We will use the actuals from last week's global report with the promises from this week.<br/>
@else
    You are not complete yet. Your regional statistician will review your sheet and declare you complete or incomplete by 8:30 am your local time Saturday morning.<br/>
@endif
<br/>
@if ($sheet)
    @include('import.results', ['sheet' => $sheet, 'includeUl' => true])
@endif
<br/>
Best,<br/>
Your regional statisticans<br/>

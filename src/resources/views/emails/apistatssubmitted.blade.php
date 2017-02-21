Hi {{ $user }},<br/>
<br/>
Thank you for submitting stats for team {{ $centerName }}.

@if ($isLate && !$isResubmitted)
Your stats are late. They were due on {{ $due->format('l, F jS \a\t g:ia') }}.
@endif

We received them on {{ $submittedAt->format('l, F jS \a\t g:ia') }} your local time.<br/>
<br/>

@if (!$isResubmitted && $respondByDateTime)
You are not complete yet. Your regional statistician will review your sheet and declare you complete by {{ $respondByDateTime->format('l \a\t g:ia') }} your local time.<br/>
@endif
<br/>

@if ($reportUrl)
    <a href="{{ $reportUrl }}">View your report online: {{ $centerName }} - {{ $reportingDate->format('M j, Y') }}</a><br/>
    <br/>
@endif
@if ($mobileDashUrl)
    Share the team's scoreboard via email or text with this mobile friendly link:
    <a href="{{ $mobileDashUrl }}">{{ $mobileDashUrl }}</a><br/>
    <br/>
@endif
@if ($comment)
    You provided the following comment:<br/>
    -----<br/>
    {{ $comment }}
    <br/>
    -----<br/>
@endif
<br/>
Best,<br/>
Your Regional Statisticians<br/>

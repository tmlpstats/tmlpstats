@extends('template')

@section('content')
    <h1>Team {{ $center->name }}</h1>
    <p>Welcome to Team {{ $center->name }}'s dashboard. Here you'll find details about your team's stats.</p>

    @if (!$statsReport)
        <p>
            You can use the <i>Validate</i> link in the menu above to validate and submit your team statistics.
            Once your team has submitted stats, a summary will appear below with a link to view the full details.
        </p>
    @elseif (!$statsReport->isValidated())
        <div class="row">
            <div class="col-md-5" style="align-content: center">
                <h3>
                    Stats from {{ $statsReport->reportingDate->format('M j, Y') }}
                    <small>(<a href="{{ $reportUrl }}">View Report</a>)</small>
                </h3>
            </div>
        </div>
        <p>
            This last report did not pass validation. To view your report information here, make sure your report passes
            validation before submitting.
        </p>
    @else
        <p>Below you will find the most recent statistics report from your team. Click <i>View Report</i> to see the full report details.</p>

        @include('statsreports.details.summary')

    @endif

    {{--
        List of all official stats reports
        List of all stats reports submitted

        Quarter Milestones
        This week

        Overview of movement this week
        Overview of TDO
        Overview of Application Status
        Overview of Travel/Rooming
        Days until weekend

        Rating
        Download link for last report

        Team Roster
        Team Accountables

     --}}
@endsection

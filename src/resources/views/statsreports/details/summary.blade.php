<div class="container-fluid">
    <div class="row">
        <div class="col-md-5">
            @include('reports.centergames.week', compact('reportData'))
        </div>
        <div class="col-md-3">
            <h4>TDO Attendance:</h4>
            <dl class="dl-horizontal">
                <dt>Team 1:</dt>
                <dd>{{ $tdo['team1'] }}%</dd>
                <dt>Team 2:</dt>
                <dd>{{ $tdo['team2'] }}%</dd>
                <dt>Total:</dt>
                <dd>{{ $tdo['total'] }}%</dd>
            </dl>

            <h4>GITW Effectiveness:</h4>
            <dl class="dl-horizontal">
                <dt>Team 1:</dt>
                <dd>{{ $gitw['team1'] }}%</dt>
                <dt>Team 2:</dt>
                <dd>{{ $gitw['team2'] }}%</dd>
                <dt>Total:</dt>
                <dd>{{ $gitw['total'] }}%</dd>
            </dl>

            @if ($teamWithdraws['total'])
                <h4>Team Members Withdrawn:</h4>
                <dl class="dl-horizontal">
                    <dt>Team 1:</dt>
                    <dd>{{ $teamWithdraws['team1'] }}</dd>
                    <dt>Team 2:</dt>
                    <dd>{{ $teamWithdraws['team2'] }}</dd>
                    <dt>Total:</dt>
                    <dd>{{ $teamWithdraws['total'] }}</dd>
                    @if ($teamWithdraws['ctw'])
                        <dt>In Conversation:</dt>
                        <dd>{{ $teamWithdraws['ctw'] }}</dd>
                    @endif
                </dl>
            @endif
        </div>
        <div class="col-md-4">
            <h4>Application Status:</h4>
            <dl class="dl-horizontal">
                @if ($applications['notSent'])
                    <dt>Not Sent:</dt>
                    <dd>{{ $applications['notSent'] }}</dd>
                @endif
                @if ($applications['out'])
                    <dt>Out:</dt>
                    <dd>{{ $applications['out'] }}</dd>
                @endif
                @if ($applications['waiting'])
                    <dt>Waiting Interview:</dt>
                    <dd>{{ $applications['waiting'] }}</dd>
                @endif
                @if ($applications['approved'])
                    <dt>Approved:</dt>
                    <dd>{{ $applications['approved'] }}</dd>
                @endif
                @if ($applications['wd'])
                    <dt>Withdrawn:</dt>
                    <dd>{{ $applications['wd'] }}</dd>
                @endif
                <dt>Total:</dt>
                <dd>{{ $applications['total'] }}</dd>
            </dl>

            @if ($applicationWithdraws['total'])
                <h4>Applications Withdrawn:</h4>
                <dl class="dl-horizontal">
                    <dt>Team 1:</dt>
                    <dd>{{ $applicationWithdraws['team1'] }}</dd>
                    <dt>Team 2:</dt>
                    <dd>{{ $applicationWithdraws['team2'] }}</dd>
                    <dt>Total:</dt>
                    <dd>{{ $applicationWithdraws['total'] }}</dd>
                </dl>
            @endif

            @if ($completedCourses)
                <h4>Course Results:</h4>
                <dl class="dl-horizontal">
                    @foreach ($completedCourses as $courseData)
                        <span style="text-decoration: underline">{{ $courseData->course->type }}
                            - {{ $courseData->course->startDate->format('M j') }}</span>
                        <dl class="dl-horizontal">
                            <dt>Standard Starts:</dt>
                            <dd>{{ $courseData->currentStandardStarts }}</dd>
                            <dt>Reg Fulfillment:</dt>
                            <dd>{{ round(($courseData->currentStandardStarts / $courseData->currentTer) * 100) }}%</dd>
                            <dt>Reg Effectiveness:</dt>
                            <dd>{{ $courseData->potentials ? round(($courseData->registrations / $courseData->potentials) * 100) : 0 }}%</dd>
                            <dt>Registrations:</dt>
                            <dd>{{ $courseData->registrations }}</dd>
                        </dl>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>
</div>


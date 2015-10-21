<?php
    $pointsTotal = 0;
?>
<div class="container-fluid">

    <div class="row">
        <div class="col-md-5">
            <table class="table table-condensed table-bordered table-striped centerStatsSummaryTable">
                <thead>
                <tr>
                    <th rowspan="2">&nbsp;</th>
                    <th colspan="5">{{ Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('M j') }}</th>
                </tr>
                <tr>
                    <th class="info">P</th>
                    <th>A</th>
                    <th>Gap</th>
                    <th>%</th>
                    <th>Pts</th>
                </tr>
                </thead>
                <tbody>
                @foreach (['cap','cpc','t1x','t2x','gitw','lf'] as $game)
                    <?php
                    $percent = null;
                    $gap = null;
                    ?>
                    <tr>
                        <th>{{ strtoupper($game) }}</th>
                        <td class="info" style="font-weight: bold">{{ $data['promise']->$game }}{{ ($game == 'gitw') ? '%' : '' }}</td>
                        <td style="font-weight: bold">{{ isset($data['actual']) ? $data['actual']->$game : '&nbsp;' }}{{ (isset($data['actual']) && $game == 'gitw') ? '%' : '' }}</td>
                        <?php
                        if (isset($data['actual'])) {
                            $percent = $data['promise']->$game
                                ? max(min(round(($data['actual']->$game/$data['promise']->$game) * 100), 100), 0)
                                : 0;
                            $gap = $data['promise']->$game - $data['actual']->$game;
                        }
                        ?>
                        <td>{{ ($gap !== null) ? $game == 'gitw' ? "{$gap}%" : "{$gap}" : '' }}</td>
                        <td>{{ ($percent !== null) ? "{$percent}%" : '' }}</td>
                        <td><?php
                            if ($percent !== null) {
                                $points = 0;
                                if ($percent == 100) {
                                    $points = ($game == 'cap') ? 8 : 4;
                                } else if ($percent >= 90) {
                                    $points = ($game == 'cap') ? 6 : 3;
                                } else if ($percent >= 80) {
                                    $points = ($game == 'cap') ? 4 : 2;
                                } else if ($percent >= 75) {
                                    $points = ($game == 'cap') ? 2 : 1;
                                }
                                $pointsTotal += $points;
                                echo $points;
                            }
                            ?></td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="5" style="text-align: right">Total:</th>
                    <th>{{ $pointsTotal }}</th>
                </tr>
                </tbody>
            </table>
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
                <dt>{{ $courseData->course->type }} - {{ $courseData->course->startDate->format('M j') }}
                    <dl class="dl-horizontal">
                        <dt>Standard Starts:</dt>
                        <dd>{{ $courseData->currentStandardStarts }}</dd>
                        <dt>Registration Fulfillment:</dt>
                        <dd>{{ round(($courseData->currentStandardStarts/$courseData->currentTer)*100) }}%</dd>
                        <dt>Registration Effectiveness:</dt>
                        <dd>{{ $courseData->potentials ? round(($courseData->registrations/$courseData->potentials)*100) : 0 }}%</dd>
                        <dt>Registered:</dt>
                        <dd>{{ $courseData->registrations }}</dd>
                    </dl>
                </dt>
                @endforeach
            </dl>
            @endif
        </div>
    </div>
</div>


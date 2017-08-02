<br>
<h5>Data so far this quarter</h5>
<div class="table-responsive">
    <table class="table table-condensed table-bordered table-hover">
        <thead>
        <tr>
            <th rowspan="3" class="border-right" style="vertical-align: middle">Center</th>
            <th rowspan="3" class="border-right" style="vertical-align: middle">Game</th>
            @foreach ($dates as $date)
                <?php
                    $cellText = '';
                    $cellClass = 'border-right-none border-left-none';

                    if ($date->eq($milestones['classroom1Date'])) {
                        $cellText = 'Milestone 1';
                        $cellClass = 'border-right border-left';
                    } else if ($date->eq($milestones['classroom2Date'])) {
                        $cellText = 'Milestone 2';
                        $cellClass = 'border-right border-left';
                    } else if ($date->eq($milestones['classroom3Date'])) {
                        $cellText = 'Milestone 3';
                        $cellClass = 'border-right border-left';
                    }
                ?>
                <th colspan="3" class="data-point border-right {{ $cellClass }}">{{ $cellText }}</th>
            @endforeach
            <th rowspan="2" class="data-point border-right" style="vertical-align: middle">Quarter Total</th>
        </tr>
        <tr>
            @foreach ($dates as $date)
                <th colspan="3" class="data-point border-right">{{ $date->format('F j, Y') }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($dates as $date)
                <th class="data-point" title="Promise">P</th>
                <th class="data-point" title="Actual">A</th>
                <th class="data-point" title="Registrations Per Participant">RPP</th>
            @endforeach
            <th class="data-point" title="Registrations Per Participant">RPP</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <tr>
                <th rowspan="3" class="border-right" style="vertical-align: middle">
                    {{ $centerName }}
                </th>
                <?php $game = 'cap'; ?>
                <th class="border-right">{{ strtoupper($game) }}</th>
                @foreach ($dates as $date)
                    @if (!isset($centerData[$date->toDateString()]))
                        <td class="data-point"></td>
                        <td class="data-point danger"></td>
                        <td class="data-point"></td>
                    @else
                        <?php
                            $dateStr = $date->toDateString();
                            $weekData = $centerData[$dateStr];

                            $gameData = $weekData['scoreboard']['games'][$game];
                            $actualClass = ($gameData['promise'] > $gameData['actual'])
                                ? 'danger'
                                : 'success';
                        ?>
                        <td class="data-point">{{ $gameData['promise'] }}</td>
                        <td class="data-point {{ $actualClass }}">
                            {{ isset($gameData['actual']) ? $gameData['actual'] : '&nbsp;' }}
                        </td>
                        <td class="data-point">{{ round($weekData['rpp']['net']['week'][$game], 1) }}</td>
                    @endif
                @endforeach
                <td class="data-point">{{ round($weekData['rpp']['net']['quarter'][$game], 1) }}</td>
            </tr>
            <tr>
                <?php $game = 'cpc'; ?>
                <th class="border-right">{{ strtoupper($game) }}</th>
                @foreach ($dates as $date)
                    @if (!isset($centerData[$date->toDateString()]))
                        <td class="data-point"></td>
                        <td class="data-point danger"></td>
                        <td class="data-point"></td>
                    @else
                        <?php
                            $dateStr = $date->toDateString();
                            $weekData = $centerData[$dateStr];

                            $gameData = $weekData['scoreboard']['games'][$game];
                            $actualClass = ($gameData['promise'] > $gameData['actual'])
                                ? 'danger'
                                : 'success';
                        ?>
                        <td class="data-point">{{ $gameData['promise'] }}</td>
                        <td class="data-point {{ $actualClass }}">
                            {{ isset($gameData['actual']) ? $gameData['actual'] : '&nbsp;' }}
                        </td>
                        <td class="data-point">{{ round($weekData['rpp']['net']['week'][$game], 1) }}</td>
                    @endif
                @endforeach
                <td class="data-point">{{ round($weekData['rpp']['net']['quarter'][$game], 1) }}</td>
            </tr>
            <tr class="border-bottom">
                <?php $game = 'lf'; ?>
                <th class="border-right">{{ strtoupper($game) }}</th>
                @foreach ($dates as $date)
                    @if (!isset($centerData[$date->toDateString()]))
                        <td class="data-point"></td>
                        <td class="data-point danger"></td>
                        <td class="data-point"></td>
                    @else
                        <?php
                            $dateStr = $date->toDateString();
                            $weekData = $centerData[$dateStr];

                            $gameData = $weekData['scoreboard']['games'][$game];
                            $actualClass = ($gameData['promise'] > $gameData['actual'])
                                ? 'danger'
                                : 'success';
                        ?>
                        <td class="data-point">{{ $gameData['promise'] }}</td>
                        <td class="data-point {{ $actualClass }}">
                            {{ isset($gameData['actual']) ? $gameData['actual'] : '&nbsp;' }}
                        </td>
                        <td class="data-point">{{ round($weekData['rpp']['net']['week'][$game], 1) }}</td>
                    @endif
                @endforeach
                <td class="data-point">{{ round($weekData['rpp']['net']['quarter'][$game], 1) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

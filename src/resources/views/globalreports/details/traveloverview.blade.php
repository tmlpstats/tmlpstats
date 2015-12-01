<div class="table-responsive">
    <table class="table table-condensed table-bordered applicationTable">
        <thead>
        <tr>
            <th rowspan="2" class="border-right">Center</th>
            <th colspan="4" class="data-point border-right">Incoming T1</th>
            <th colspan="4" class="data-point border-right">Incoming T2</th>
            <th colspan="4" class="data-point border-right">Current T1</th>
            <th colspan="4" class="data-point border-right">Current T2</th>
        </tr>
        <tr>
            <th colspan="2" class="data-point">Travel</th>
            <th colspan="2" class="data-point border-right">Room</th>
            <th colspan="2" class="data-point">Travel</th>
            <th colspan="2" class="data-point border-right">Room</th>
            <th colspan="2" class="data-point">Travel</th>
            <th colspan="2" class="data-point border-right">Room</th>
            <th colspan="2" class="data-point">Travel</th>
            <th colspan="2" class="data-point border-right">Room</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <tr>
                <td class="border-right">{{ $centerName }}</td>
                @foreach ($centerData as $group => $groupData)
                    @foreach ($groupData as $team => $teamData)
                        @if ($teamData['total'])
                            <?php
                            $travelPercent = round(($teamData['travel'] / $teamData['total']) * 100);
                            $roomPercent = round(($teamData['room'] / $teamData['total']) * 100);

                            $travelClass = '';
                            if ($travelPercent == 100) {
                                $travelClass = 'bg-success';
                            } else if ($travelPercent < 75) {
                                $travelClass = 'bg-danger';
                            } else {
                                $travelClass = 'bg-warning';
                            }

                            $roomClass = '';
                            if ($roomPercent == 100) {
                                $roomClass = 'bg-success';
                            } else if ($roomPercent < 75) {
                                $roomClass = 'bg-danger';
                            } else {
                                $roomClass = 'bg-warning';
                            }
                            ?>
                            <td class="data-point">{{ $teamData['travel'] }}/{{ $teamData['total'] }}</td>
                            <td class="data-point" class="{{ $travelClass }}">{{ $travelPercent }}%</td>
                            <td class="data-point">{{ $teamData['room'] }}/{{ $teamData['total'] }}</td>
                            <td class="data-point border-right" class="{{ $roomClass }}">{{ $roomPercent }}%</td>
                        @else
                            <td colspan="2" class="data-point">N/A</td>
                            <td colspan="2" class="data-point border-right">N/A</td>
                        @endif
                    @endforeach
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

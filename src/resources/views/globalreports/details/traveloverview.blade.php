<div class="table-responsive">
    <table class="table table-condensed applicationTable">
        <thead>
        <tr>
            <th rowspan="2" style="border-right: 2px solid #DDD;">Center</th>
            <th colspan="4" style="text-align: center; border-right: 2px solid #DDD;">Incoming T1</th>
            <th colspan="4" style="text-align: center; border-right: 2px solid #DDD;">Incoming T2</th>
            <th colspan="4" style="text-align: center; border-right: 2px solid #DDD;">Current T1</th>
            <th colspan="4" style="text-align: center; border-right: 2px solid #DDD;">Current T2</th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center; border-right: 1px solid #DDD;">Travel</th>
            <th colspan="2" style="text-align: center; border-right: 2px solid #DDD;">Room</th>
            <th colspan="2" style="text-align: center; border-right: 1px solid #DDD;">Travel</th>
            <th colspan="2" style="text-align: center; border-right: 2px solid #DDD;">Room</th>
            <th colspan="2" style="text-align: center; border-right: 1px solid #DDD;">Travel</th>
            <th colspan="2" style="text-align: center; border-right: 2px solid #DDD;">Room</th>
            <th colspan="2" style="text-align: center; border-right: 1px solid #DDD;">Travel</th>
            <th colspan="2" style="text-align: center; border-right: 2px solid #DDD;">Room</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <tr>
                <td style="border-right: 2px solid #DDD;">{{ $centerName }}</td>
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
                            <td style="text-align: center; border-right: 1px solid #DDD;">{{ $teamData['travel'] }}/{{ $teamData['total'] }}</td>
                            <td style="text-align: center; border-right: 1px solid #DDD;" class="{{ $travelClass }}">{{ $travelPercent }}%</td>
                            <td style="text-align: center; border-right: 1px solid #DDD;">{{ $teamData['room'] }}/{{ $teamData['total'] }}</td>
                            <td style="text-align: center; border-right: 2px solid #DDD;" class="{{ $roomClass }}">{{ $roomPercent }}%</td>
                        @else
                            <td colspan="2" style="text-align: center; border-right: 1px solid #DDD;">N/A</td>
                            <td colspan="2" style="text-align: center; border-right: 2px solid #DDD;">N/A</td>
                        @endif
                    @endforeach
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

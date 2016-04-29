<div class="table-responsive">
    <br>
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th rowspan="2" class="data-point border-left border-right">Game</th>
            @foreach ($regions as $region)
            <th colspan="5" class="data-point border-right">{{ $region->name }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($regions as $region)
                <th class="data-point">Promise</th>
                <th class="data-point">Actual</th>
                <th class="data-point">%</th>
                <th class="data-point">Points</th>
                <th class="data-point border-right">RPP *</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
            @foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game)
                <tr>
                    <th class="border-left border-right">{{ strtoupper($game) }}</th>
                    @foreach ($regionsData as $name => $data)
                        <?php
                        if ($data['percent'][$game] < 50) {
                            $effectivenessClass = 'danger';
                        } else if ($data['percent'][$game] < 75) {
                            $effectivenessClass = 'warning';
                        } else {
                            $effectivenessClass = 'success';
                        }

                        if ($game === 'gitw') {
                            $rppClass = '';
                        } else if ($rpp[$name][$game] < 1) {
                            $rppClass = 'error';
                        } else {
                            $rppClass = 'ok';
                        }
                        ?>
                        <td class="data-point">{{ $data['promise'][$game] }}</td>
                        <td class="data-point">{{ $data['actual'][$game] }}</td>
                        <td class="data-point {{ $effectivenessClass }}">{{ $data['percent'][$game] }}%</td>
                        <td class="data-point">{{ $data['points'][$game] }}</td>
                        <td class="data-point border-right {{ $rppClass }}">{{ $game === 'gitw' ? "--" : number_format($rpp[$name][$game], 2) }}</td>
                    @endforeach
                </tr>
            @endforeach
            <tr class="border-top">
                <th class="border-right">&nbsp;</th>
                @foreach ($regionsData as $name => $data)
                    <th colspan="3" class="data-point"><span style="text-transform: uppercase">{{ $data['rating'] }}</span></th>
                    <th class="data-point">{{ $data['points']['total'] }}</th>
                    <th class="border-right">&nbsp;</th>
                @endforeach
            </tr>
            <tr>
                <td colspan="{{ 1 + 5 * count($regions) }}">
                    <span style="color: red; font-size: small">* RPP (Registrations Per Participant) = Number of registrations / Number of current team members</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

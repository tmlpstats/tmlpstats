<?php
if (!function_exists('getPercentClass')) {
    function getPercentClass($percent) {
        if ($percent < 50) {
            return 'danger';
        } else if ($percent < 75) {
            return 'warning';
        }
        return 'success';
    }
}
?>
<br>
<div class="row">
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
                    @if (!isset($regionsData[$name][$globalReport->reportingDate->toDateString()]))
                        <td class="data-point"></td>
                        <td class="data-point"></td>
                        <td class="data-point"></td>
                        <td class="data-point"></td>
                        <td class="data-point border-right"></td>
                    @else
                        <?php
                        $data = $regionsData[$name][$globalReport->reportingDate->toDateString()];
                        $effectivenessClass = getPercentClass($data['percent'][$game]);

                        if ($game === 'gitw') {
                            $rppClass = '';
                        } else if ($rpp[$name][$game] < 1) {
                            $rppClass = 'error';
                        } else {
                            $rppClass = 'ok';
                        }
                        $suffix = ($game === 'gitw') ? '%' : '';
                        ?>
                        <td class="data-point">{{ $data['promise'][$game] }}{{ $suffix }}</td>
                        <td class="data-point">{{ $data['actual'][$game] }}{{ $suffix }}</td>
                        <td class="data-point {{ $effectivenessClass }}">{{ $data['percent'][$game] }}%</td>
                        <td class="data-point">{{ $data['points'][$game] }}</td>
                        <td class="data-point border-right {{ $rppClass }}">{{ $game === 'gitw' ? "--" : number_format($rpp[$name][$game], 2) }}</td>
                    @endif
                    @endforeach
                </tr>
            @endforeach
            <tr class="border-top">
                <th class="border-right">&nbsp;</th>
                @foreach ($regionsData as $name => $data)
                @if (!isset($regionsData[$name][$globalReport->reportingDate->toDateString()]))
                    <th colspan="3" class="data-point"></th>
                    <th class="data-point"></th>
                @else
                    <?php
                    $data = $regionsData[$name][$globalReport->reportingDate->toDateString()];
                    $effectivenessClass = getPercentClass($data['percent']['total']);
                    ?>
                    <th colspan="2" class="data-point"><span style="text-transform: uppercase">{{ $data['rating'] }}</span></th>
                    <th class="data-point {{ $effectivenessClass }}">{{ $data['percent']['total'] }}%</th>
                    <th class="data-point">{{ $data['points']['total'] }}</th>
                @endif

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
<div class="row">
    @include('reports.charts.ratings.chart')
</div>
<div class="row">
    @include('reports.charts.percentages.chart', ['divId' => 'percent-container'])
</div>

<!-- SCRIPTS_FOLLOW -->
@include('reports.charts.ratings.setup')
@include('reports.charts.percentages.setup', ['divId' => 'percent-container'])

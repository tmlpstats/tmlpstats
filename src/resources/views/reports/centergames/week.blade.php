<div class="table-responsive">
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
            <tr>
                <th>{{ strtoupper($game) }}</th>
                <td class="info"
                    style="font-weight: bold">{{ $reportData['promise'][$game] }}{{ ($game == 'gitw') ? '%' : '' }}</td>
                <td style="font-weight: bold">{{ isset($reportData['actual']) ? $reportData['actual'][$game] : '&nbsp;' }}{{ (isset($reportData['actual']) && $game == 'gitw') ? '%' : '' }}</td>
                <?php
                $gap = isset($reportData['actual'])
                    ? $reportData['promise'][$game] - $reportData['actual'][$game]
                    : null;
                ?>
                <td>{{ ($gap !== null) ? $game == 'gitw' ? "{$gap}%" : "{$gap}" : '' }}</td>
                <td>{{ "{$reportData['percent'][$game]}%" }}</td>
                <td>{{ $reportData['points'][$game] }}</td>
            </tr>
        @endforeach
        <tr>
            <th colspan="4">{{ $reportData['rating'] }}</th>
            <th style="text-align: right">Total:</th>
            <th>{{ $reportData['points']['total'] }}</th>
        </tr>
        </tbody>
    </table>
</div>

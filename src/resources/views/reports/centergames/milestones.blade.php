<?php
    $maxCount = count($reportData);
    foreach ($reportData as $chunk) {
        $chunkCount = count($chunk);
        if ($chunkCount > $maxCount) {
            $maxCount = $chunkCount;
        }
    }
?>
@if (!$reportData)
    <p>No game information available.</p>
@else
    <div class="container">
            @for ($k = 0; $k < 2; $k++)
                <div class="row">
                    @for ($j = $k; $j < $k+2; $j++)
                        <div class="col-lg-6">
                            <table class="table table-condensed table-bordered table-striped table-hover centerStatsTable">
                                <thead>
                                <tr>
                                    <th rowspan="2">&nbsp;</th>
                                    @foreach ($reportData[$j+$k] as $date => $data)
                                        <th colspan="2" style="min-width: 5.5em">{{ Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('M j') }}</th>
                                    @endforeach
                                    @for ($i = count($reportData[$j+$k]); $i <= $maxCount -1; $i++)
                                        <th colspan="2" style="min-width: 5.5em"></th>
                                    @endfor
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    @foreach ($reportData[$j+$k] as $date => $data)
                                        <th class="info">P</th>
                                        <th>A</th>
                                    @endforeach
                                    @for ($i = count($reportData[$j+$k]); $i <= $maxCount - 1; $i++)
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    @endfor
                                    <th>%</th>
                                    <th>Pts</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $lastWeek = null;
                                ?>
                                @foreach (['cap','cpc','t1x','t2x','gitw','lf'] as $game)
                                    <tr>
                                        <th>{{ strtoupper($game) }}</th>
                                        @foreach ($reportData[$j+$k] as $date => $data)
                                            <td class="info">{{ $data['promise'][$game] }}{{ ($game == 'gitw') ? '%' : '' }}</td>
                                            <td>{{ isset($data['actual']) ? $data['actual'][$game] : '&nbsp;&nbsp;&nbsp;&nbsp;' }}{{ (isset($data['actual']) && $game == 'gitw') ? '%' : '' }}</td>
                                            <?php
                                            if (isset($data['actual'])) {
                                                $lastWeek = $data;
                                            }
                                            ?>
                                        @endforeach
                                        @for ($i = count($reportData[$j+$k]); $i <= $maxCount -1; $i++)
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                        @endfor
                                        <td>{{ isset($lastWeek['percent'][$game]) ? "{$lastWeek['percent'][$game]}%" : '' }}</td>
                                        <td>{{ isset($lastWeek['points'][$game]) ? $lastWeek['points'][$game] : '' }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    @if (isset($lastWeek['rating']))
                                        <th colspan="{{ $maxCount * 2 + 1 }}">{{ $lastWeek['rating'] }}</th>
                                        <th style="text-align: right">Total:</th>
                                        <th>{{ $lastWeek['points']['total'] }}</th>
                                    @else
                                        <th colspan="{{ $maxCount * 2 + 3 }}">&nbsp;</th>
                                    @endif
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    @endfor
                </div>
            @endfor
    </div>
@endif

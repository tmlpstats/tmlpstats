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
    <div class="table-responsive">
        <table>
            @for ($k = 0; $k < 2; $k++)
                <tr>
                    @for ($j = $k; $j < $k+2; $j++)
                        <td>
                            <table class="table table-condensed table-bordered table-striped table-hover centerStatsTable">
                                <thead>
                                <tr>
                                    <th rowspan="2">&nbsp;</th>
                                    @foreach ($reportData[$j+$k] as $date => $data)
                                        <th colspan="2" width="{{ round(500/$maxCount) }}">{{ Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('M j') }}</th>
                                    @endforeach
                                    @for ($i = count($reportData[$j+$k]); $i <= $maxCount -1; $i++)
                                        <th colspan="2" width="{{ round(500/$maxCount) }}"></th>
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
                                            <td>{{ isset($data['actual']) ? $data['actual'][$game] : '&nbsp;' }}{{ (isset($data['actual']) && $game == 'gitw') ? '%' : '' }}</td>
                                            <?php
                                            if (isset($data['actual'])) {
                                                $lastWeek = $data;
                                            }
                                            ?>
                                        @endforeach
                                        @for ($i = count($reportData[$j+$k]); $i <= $maxCount -1; $i++)
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
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
                        </td>
                    @endfor
                </tr>
            @endfor
        </table>
    </div>
@endif

<?php
$maxCount = count($centerStatsData);
foreach ($centerStatsData as $chunk) {
    $chunkCount = count($chunk);
    if ($chunkCount > $maxCount) {
        $maxCount = $chunkCount;
    }
}
?>
<div class="table-responsive">
    <h3>Center Stats</h3>
    <table>
        @for ($k = 0; $k < 2; $k++)
            <tr>
                @for ($j = 0; $j < 2; $j++)
                    <td class="centerStatsFrame">
                        <table class="table table-condensed table-bordered table-striped centerStatsTable">
                            <thead>
                                <tr>
                                    <th rowspan="2">&nbsp;</th>
                                    @foreach ($centerStatsData[$j+$k] as $date => $data)
                                        <th colspan="2" width="{{ round(500/$maxCount) }}">{{ Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('M j') }}</th>
                                    @endforeach
                                    @for ($i = count($centerStatsData[$j+$k]); $i <= $maxCount -1; $i++)
                                        <th colspan="2" width="{{ round(500/$maxCount) }}"></th>
                                    @endfor
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    @foreach ($centerStatsData[$j+$k] as $date => $data)
                                        <th class="info">P</th>
                                        <th>A</th>
                                    @endforeach
                                    @for ($i = count($centerStatsData[$j+$k]); $i <= $maxCount - 1; $i++)
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    @endfor
                                    <th>%</th>
                                    <th>Pts</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach (['cap','cpc','t1x','t2x','gitw','lf'] as $game)
                                <?php
                                    $percent = null;
                                ?>
                                <tr>
                                    <th>{{ strtoupper($game) }}</th>
                                    @foreach ($centerStatsData[$j+$k] as $date => $data)
                                        <td class="info">{{ $data['promise']->$game }}{{ ($game == 'gitw') ? '%' : '' }}</td>
                                        <td>{{ isset($data['actual']) ? $data['actual']->$game : '&nbsp;' }}{{ (isset($data['actual']) && $game == 'gitw') ? '%' : '' }}</td>
                                        <?php
                                            if (isset($data['actual'])) {
                                                $percent = max(min(round(($data['actual']->$game/$data['promise']->$game) * 100), 100), 0);
                                            }
                                        ?>
                                    @endforeach
                                    @for ($i = count($centerStatsData[$j+$k]); $i <= $maxCount -1; $i++)
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    @endfor
                                    <td>{{ ($percent !== null) ? "{$percent}%" : '' }}</td>
                                    <td><?php
                                        if ($percent !== null) {
                                            if ($percent == 100) {
                                                echo ($game == 'cap') ? 8 : 4;
                                            } else if ($percent >= 90) {
                                                echo ($game == 'cap') ? 6 : 3;
                                            } else if ($percent >= 80) {
                                                echo ($game == 'cap') ? 4 : 2;
                                            } else if ($percent >= 75) {
                                                echo ($game == 'cap') ? 2 : 1;
                                            } else {
                                                echo 0;
                                            }
                                        }
                                    ?></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </td>
                @endfor
            </tr>
        @endfor
    </table>
</div>

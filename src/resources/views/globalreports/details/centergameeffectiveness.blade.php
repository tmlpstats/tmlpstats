<?php
    $dates = array_keys(reset($reportData));
    foreach ($dates as $i => $dateStr) {
        $dates[$i] = Carbon\Carbon::parse($dateStr);
    }
?>
<br>
<h5>Data so far this quarter</h5>
<div class="table-responsive">
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th rowspan="2" class="border-right" style="vertical-align: middle">Center</th>
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
                <th class="data-point border-right {{ $cellClass }}">{{ $cellText }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($dates as $date)
                <th class="data-point border-right">{{ $date->format('F j, Y') }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $weekData)
        <tr>
            <th class="border-right">
                {{ $centerName }}
            </th>
            @foreach ($weekData as $week => $centerData)
                <?php
                    $actualClass = $centerData['effective'] ? 'success' : 'bg-danger';
                ?>
                <td class="data-point border-right {{ $actualClass }}">
                    {{ $centerData['actual'] }}{{ ($game == 'gitw') ? '%' : '' }}
                    of
                    {{ $centerData['promise'] }}{{ ($game == 'gitw') ? '%' : '' }}
                </td>
            @endforeach
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

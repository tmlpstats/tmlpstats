<br>
<div class="row">
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th rowspan="2" class="data-point border-left border-right">Game</th>
            @foreach ($regions as $region)
            <th colspan="3" class="data-point border-right">{{ $region->name }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($regions as $region)
                <th class="data-point">Promise</th>
                <th class="data-point">Actual</th>
                <th class="data-point success">Gap</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
            @foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game)
                <tr>
                    <th class="border-left border-right">{{ strtoupper($game) }}</th>
                    @foreach ($regionsData as $name => $data)
                        <?php
                            $promise = isset($data['promise'][$game]) ? $data['promise'][$game] : '-';
                            $actual = isset($data['actual'][$game]) ? $data['actual'][$game] : '-';
                            if ($promise == '-' || $actual == '-') {
                                $gap = '-';
                                $suffix = '';
                            } else {
                                $gap = $promise - $actual;
                                $suffix = ($game === 'gitw') ? '%' : '';
                            }
                        ?>
                        <td class="data-point">{{ $promise }}{{ $suffix }}</td>
                        <td class="data-point">{{ $actual }}{{ $suffix }}</td>
                        <td class="data-point success">{{ $gap }}{{ $suffix }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

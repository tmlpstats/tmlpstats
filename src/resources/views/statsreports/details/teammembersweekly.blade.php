<div class="table-responsive">
    <br/>
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th style="vertical-align: middle">Name</th>
            <th class="data-point" style="width: 5em">Team Year</th>
            @if ($type === 'tdo')
                <th class="data-point" style="width: 5em">Total</th>
            @endif
            @foreach ($reportData['dates'] as $date)
                <th class="data-point">{{ $date->format('M j') }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData['members'] as $id => $memberRow)
            @if ($memberRow['withdrawn'] == false)
                <tr>
                    <td>{{ $memberRow['member']->firstName }} {{ $memberRow['member']->lastName }}</td>
                    <td class="data-point">T{{ $memberRow['member']->teamYear }}Q{{ $memberRow['member']->quarterNumber }}</td>
                    @if ($type === 'tdo')
                        <td class="data-point">{{ $memberRow['total'] }} / {{ count($reportData['dates']) }}</td>
                    @endif
                    @foreach ($reportData['dates'] as $date)
                        <?php $data = isset($memberRow[$date->toDateString()]) ? $memberRow[$date->toDateString()] : null; ?>
                        @if ($data === null)
                            <td class="data-point active">
                                <span class="glyphicon glyphicon-minus"></span>
                            </td>
                        @elseif ($data['value'])
                            <td class="data-point success">
                                @if ($type === 'tdo')
                                    <span class="glyphicon numeric-glyphicon">{{ $data['value'] }}</span>
                                @else
                                    <span class="glyphicon glyphicon-ok"></span>
                                @endif
                            </td>
                        @else
                            <td class="data-point danger">
                                @if ($type === 'tdo')
                                    <span class="glyphicon numeric-glyphicon">{{ $data['value'] }}</span>
                                @else
                                    <span class="glyphicon glyphicon-remove"></span>
                                @endif
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>

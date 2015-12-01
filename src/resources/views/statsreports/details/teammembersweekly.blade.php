<div class="table-responsive">
    <br/>
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th style="vertical-align: middle">Name</th>
            <th  class="data-point" style="width: 5em">Team Year</th>
            @foreach ($reportData['dates'] as $date)
                <th  class="data-point">{{ $date->format('M j') }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData['members'] as $id => $memberRow)
            @if ($memberRow['withdrawn'] == false)
                <tr>
                    <td>{{ $memberRow['member']->firstName }} {{ $memberRow['member']->lastName }}</td>
                    <td class="data-point">T{{ $memberRow['member']->teamYear }}Q{{ $memberRow['member']->quarterNumber }}</td>
                    @foreach ($reportData['dates'] as $date)
                        <?php $data = isset($memberRow[$date->toDateString()]) ? $memberRow[$date->toDateString()] : null; ?>
                        @if ($data === null)
                            <td class="data-point active">
                                <span class="glyphicon glyphicon-minus"></span>
                            </td>
                        @elseif ($data['value'])
                            <td class="data-point success">
                                <span class="glyphicon glyphicon-ok"></span>
                            </td>
                        @else
                            <td class="data-point danger">
                                <span class="glyphicon glyphicon-remove"></span>
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>

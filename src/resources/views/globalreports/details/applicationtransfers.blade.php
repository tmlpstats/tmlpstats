<div class="table-responsive">
    <br/>
    <h4>Applications Overview</h4>
    <table class="table table-condensed table-striped table-hover transferTable want-datatable">
        <thead>
        <tr>
            <th class="data-point">Center</th>
            <th class="data-point">Transfer Date</th>
            <th class="data-point">Name</th>
            <th class="data-point">From</th>
            <th class="data-point">To</th>
            <th class="data-point">Transfer Count</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            @foreach ($centerData as $data)
                @foreach ($data as $transfer)
                <tr>
                    <td class="border-right">
                        {{ $centerName }}
                    </td>
                    <td class="data-point">{{ $transfer['reportingDate'] }}</td>
                    <td class="data-point">{{ $transfer['name'] }}</td>
                    <td class="data-point">{{ $transfer['from'] }}</td>
                    <td class="data-point">{{ $transfer['to'] }}</td>
                    <td class="data-point">{{ count($data) }}</td>
                </tr>
                @endforeach
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>

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
            <th class="data-point">Last Comment</th>
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
                    <td class="data-point border-right-thin">{{ $transfer['reportingDate'] }}</td>
                    <td class="border-right-thin">{{ $transfer['name'] }}</td>
                    <td class="border-right-thin">{{ $transfer['from'] }}</td>
                    <td class="border-right-thin">{{ $transfer['to'] }}</td>
                    <td class="border-right-thin">{{ $transfer['comment'] }}</td>
                    <td class="data-point">{{ count($data) }}</td>
                </tr>
                @endforeach
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>

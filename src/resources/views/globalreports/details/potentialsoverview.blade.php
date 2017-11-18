<div class="table-responsive">
    <br/>
    <h4>Overview</h4>
    <table id="potentialsOverviewTable" class="table table-condensed table-striped table-hover want-datatable">
        <thead>
        <tr>
            <th class="border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right" colspan="2">Total</th>
            @foreach ($quarters as $qid => $q)
                <th class="data-point border-right" colspan="2">{{ $q->startWeekendDate->format('F Y') }}</th>
            @endforeach

        </tr>
        <tr>
            <th class="border-right">Center</th>
            <th class="data-point">T1Q4</th>
            <th class="data-point">Registered</th>
            <th class="data-point border-right">Approved</th>
            @foreach ($quarters as $qid => $label)
                <th class="data-pint">Reg</th>
                <th class="data-oint border-right">Appr.</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <tr>
                <td class="border-right">
                    @statsReportLink($statsReports[$centerName])
                        {{ $centerName }}
                    @endStatsReportLink
                </td>
                <td class="data-point">{{ $centerData['total'] }}</td>
                <td class="data-point">{{ $centerData['registered'] }}</td>
                <td class="data-point border-right">{{ $centerData['approved'] }}</td>
                @foreach ($quarters as $qid => $ignore)
                    <td class="data-point">{{ $centerData["registered{$qid}"] ?? '0' }}</td>
                    <td class="data-point border-right">{{ $centerData["approved{$qid}"] ?? '-' }}</td>
                @endforeach

            </tr>
        @endforeach
        </tbody>
        {{-- This is pretty janky, but putting this row outside of the tbody prevents datatables from including it in the sort --}}
        <tr style="font-weight:bold">
            <td class="border-right">Totals</td>
            <td class="data-point">{{ $totals['total'] }}</td>
            <td class="data-point">{{ $totals['registered'] }}</td>
            <td class="data-point">{{ $totals['approved'] }}</td>
            @foreach ($quarters as $qid => $ignore)
                <td class="data-point">{{ $totals["registered{$qid}"] ?? '0' }}</td>
                <td class="data-point border-right">{{ $totals["approved{$qid}"] ?? '0' }}</td>
            @endforeach
        </tr>
    </table>
</div>

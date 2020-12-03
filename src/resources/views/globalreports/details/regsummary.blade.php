<div>
    <ul class="nav nav-pills">
        <li role="presentation" class="active"><a href="#Total" aria-controls="Total" role="tab" data-toggle="tab">Total</a></li>
        <li role="presentation"><a href="#LF" aria-controls="LF" role="tab" data-toggle="tab">LF</a></li>
        <li role="presentation"><a href="#CAP" aria-controls="CAP" role="tab" data-toggle="tab">CAP</a></li>
        <li role="presentation"><a href="#CPC" aria-controls="CPC" role="tab" data-toggle="tab">CPC</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="Total">
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-hover">
                    <thead>
                    <tr>
                        <th rowspan="2" class="border-left border-right border-top">Center</th>
                        <th colspan="3" class="data-point border-right border-top">Team 1</th>
                        <th colspan="3" class="data-point border-right border-top">Team 2</th>
                        <th colspan="3" class="data-point border-right border-top">Total</th>
                    </tr>
                    <tr>
                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reportData as $centerName => $centerData)
                        <tr>
                            <td class="border-left border-right">
                                @statsReportLink($statsReports[$centerName],'/TeamMembers/ClassList')
                                {{ $centerName }}
                                @endStatsReportLink
                            </td>
                            <td class="data-point">{{ $centerData['team1']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team1']['total'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team1']['total_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['team2']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team2']['total'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team2']['total_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['total']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['total']['total'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['total']['total_rpp'], 2, '.', '') }}</td>

                        </tr>
                    @endforeach
                    </tbody>
                    <tr>
                        <td class="data-point border-left border-right border-bottom"></td>

                        <td class="data-point border-bottom">{{ $totals['team1']['member_count'] }}</td>
                        <td class="data-point border-right border-bottom">{{ $totals['team1']['total'] }}</td>
                        <td class="data-point border-right border-bottom">{{ number_format((float)$totals['team1']['total_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['team2']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['team2']['total'] }}</td>
                        <td class="data-point border-bottom border-right">{{ number_format((float)$totals['team2']['total_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['total']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['total']['total'] }}</td>
                        <td class="data-point border-bottom
                        border-right">{{ number_format((float)$totals['total']['total_rpp'], 2, '.', '') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="LF">
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-hover">
                    <thead>
                    <tr>
                        <th rowspan="2" class="border-left border-right border-top">Center</th>
                        <th colspan="3" class="data-point border-right border-top">Team 1</th>
                        <th colspan="3" class="data-point border-right border-top">Team 2</th>
                        <th colspan="3" class="data-point border-right border-top">Total</th>
                    </tr>
                    <tr>
                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reportData as $centerName => $centerData)
                        <tr>
                            <td class="border-left border-right">
                                @statsReportLink($statsReports[$centerName])
                                {{ $centerName }}
                                @endStatsReportLink
                            </td>
                            <td class="data-point">{{ $centerData['team1']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team1']['lf'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team1']['lf_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['team2']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team2']['lf'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team2']['lf_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['total']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['total']['lf'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['total']['lf_rpp'], 2, '.', '') }}</td>

                        </tr>
                    @endforeach
                    </tbody>
                    <tr>
                        <td class="data-point border-left border-right border-bottom"></td>

                        <td class="data-point border-bottom">{{ $totals['team1']['member_count'] }}</td>
                        <td class="data-point border-right border-bottom">{{ $totals['team1']['lf'] }}</td>
                        <td class="data-point border-right border-bottom">{{ number_format((float)$totals['team1']['lf_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['team2']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['team2']['lf'] }}</td>
                        <td class="data-point border-bottom border-right">{{ number_format((float)$totals['team2']['lf_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['total']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['total']['lf'] }}</td>
                        <td class="data-point border-bottom
                        border-right">{{ number_format((float)$totals['total']['lf_rpp'], 2, '.', '') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="CAP">
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-hover">
                    <thead>
                    <tr>
                        <th rowspan="2" class="border-left border-right border-top">Center</th>
                        <th colspan="3" class="data-point border-right border-top">Team 1</th>
                        <th colspan="3" class="data-point border-right border-top">Team 2</th>
                        <th colspan="3" class="data-point border-right border-top">Total</th>
                    </tr>
                    <tr>
                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reportData as $centerName => $centerData)
                        <tr>
                            <td class="border-left border-right">
                                @statsReportLink($statsReports[$centerName])
                                {{ $centerName }}
                                @endStatsReportLink
                            </td>
                            <td class="data-point">{{ $centerData['team1']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team1']['cap'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team1']['cap_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['team2']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team2']['cap'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team2']['cap_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['total']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['total']['cap'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['total']['cap_rpp'], 2, '.', '') }}</td>

                        </tr>
                    @endforeach
                    </tbody>
                    <tr>
                        <td class="data-point border-left border-right border-bottom"></td>

                        <td class="data-point border-bottom">{{ $totals['team1']['member_count'] }}</td>
                        <td class="data-point border-right border-bottom">{{ $totals['team1']['cap'] }}</td>
                        <td class="data-point border-right border-bottom">{{ number_format((float)$totals['team1']['cap_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['team2']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['team2']['cap'] }}</td>
                        <td class="data-point border-bottom border-right">{{ number_format((float)$totals['team2']['cap_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['total']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['total']['cap'] }}</td>
                        <td class="data-point border-bottom border-right">{{ number_format((float)$totals['total']['cap_rpp'], 2, '.', '') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="CPC">
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-hover">
                    <thead>
                    <tr>
                        <th rowspan="2" class="border-left border-right border-top">Center</th>
                        <th colspan="3" class="data-point border-right border-top">Team 1</th>
                        <th colspan="3" class="data-point border-right border-top">Team 2</th>
                        <th colspan="3" class="data-point border-right border-top">Total</th>
                    </tr>
                    <tr>
                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>

                        <th class="data-point"># of Members</th>
                        <th class="data-point border-right">Registrations</th>
                        <th class="data-point border-right">RPP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reportData as $centerName => $centerData)
                        <tr>
                            <td class="border-left border-right">
                                @statsReportLink($statsReports[$centerName])
                                {{ $centerName }}
                                @endStatsReportLink
                            </td>
                            <td class="data-point">{{ $centerData['team1']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team1']['cpc'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team1']['cpc_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['team2']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['team2']['cpc'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['team2']['cpc_rpp'], 2, '.', '') }}</td>

                            <td class="data-point">{{ $centerData['total']['member_count'] }}</td>
                            <td class="data-point border-right">{{ $centerData['total']['cpc'] }}</td>
                            <td class="data-point border-right">{{ number_format((float)$centerData['total']['cpc_rpp'], 2, '.', '') }}</td>

                        </tr>
                    @endforeach
                    </tbody>
                    <tr>
                        <td class="data-point border-left border-right border-bottom"></td>

                        <td class="data-point border-bottom">{{ $totals['team1']['member_count'] }}</td>
                        <td class="data-point border-right border-bottom">{{ $totals['team1']['cpc'] }}</td>
                        <td class="data-point border-right border-bottom">{{ number_format((float)$totals['team1']['cpc_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['team2']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['team2']['cpc'] }}</td>
                        <td class="data-point border-bottom border-right">{{ number_format((float)$totals['team2']['cpc_rpp'], 2, '.', '') }}</td>

                        <td class="data-point border-bottom">{{ $totals['total']['member_count'] }}</td>
                        <td class="data-point border-bottom border-right">{{ $totals['total']['cpc'] }}</td>
                        <td class="data-point border-bottom border-right">{{ number_format((float)$totals['total']['cpc_rpp'], 2, '.', '') }}</td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

</div>


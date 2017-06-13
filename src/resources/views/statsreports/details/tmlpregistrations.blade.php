<div class="table-responsive">
    @foreach (['team1', 'team2', 'withdrawn'] as $group)
        @if (isset($reportData[$group]) && count($reportData[$group]))
            <br/>
            <h4>{{ ucwords($group) }}</h4>
            <?php
            foreach (['next', 'future'] as $quarterName) {
                if (!isset($reportData[$group][$quarterName])) {
                    continue;
                }
                $quarterRegistrations = $reportData[$group][$quarterName];
            ?>
                <table class="table table-condensed table-striped table-hover want-datatable">
                    <thead>
                    @if ($group != 'withdrawn')
                        <tr>
                            <th colspan="14">Starting {{ ucwords($quarterName) }} Quarter</th>
                        </tr>
                    @endif
                    <tr>
                        <th>First<span class="hs"> Name</span></th>
                        <th>Last<span class="hs"> Name</span></th>
                        <th class="data-point">Reg<span class="hs">istered</span></th>
                        <th class="data-point">App Out</th>
                        <th class="data-point">App In</th>
                        <th class="data-point">Approve</th>
                        @if ($group == 'withdrawn')
                            <th class="data-point">Reason</th>
                            <th class="data-point">Withdraw Date</th>
                        @endif
                        <th class="data-point">Comments</th>
                        <th class="hs">Committed Team Member</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($quarterRegistrations as $registrationData)
                        <tr>
                            <td>{{ $registrationData->firstName }}</td>
                            <td>{{ $registrationData->lastName }}</td>
                            <td class="data-point">
                                @if ($registrationData->regDate)
                                    @date($registrationData->regDate)
                                @endif
                            </td>
                            <td class="data-point">
                                @if ($registrationData->appOutDate)
                                    @date($registrationData->appOutDate)
                                @endif
                            </td>
                            <td class="data-point">
                                @if ($registrationData->appInDate)
                                    @date($registrationData->appInDate)
                                @endif
                            </td>
                            <td class="data-point">
                                @if ($registrationData->apprDate)
                                    @date($registrationData->apprDate)
                                @endif
                            </td>
                            @if ($group == 'withdrawn')
                                @if ($registrationData->withdrawCode)
                                    <td class="data-point" title="{{ $registrationData->withdrawCode->display }}">
                                        {{ $registrationData->withdrawCode->code }}
                                    </td>
                                    <td class="data-point" title="{{ $registrationData->withdrawCode->display }}">
                                        @if ($registrationData->wdDate)
                                            @date($registrationData->wdDate)
                                        @endif
                                    </td>
                                @else
                                    <td></td>
                                    <td></td>
                                @endif
                            @endif
                            <?php
                                $comment = is_numeric($registrationData->comment)
                                    ? TmlpStats\Util::getExcelDate($registrationData->comment)->format('F')
                                    : $registrationData->comment;
                            ?>
                            @if ($comment)
                                <td class="data-point comments">
                                    <span data-toggle="tooltip" class="hl glyphicon glyphicon-th-list" title="{{ $comment }}"></span>
                                    <span class="hs">{{ $comment }}</span>
                                </td>
                            @else
                                <td>&nbsp;</td>
                            @endif
                            <td class="hs">{{ $registrationData->committedTeamMember ? $registrationData->committedTeamMember->firstName . ' ' . $registrationData->committedTeamMember->lastName : '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            <?php } ?>
        @endif
    @endforeach
</div>
<!-- SCRIPTS_FOLLOW -->
<script>
$(document).ready(function() {
  $('[data-toggle="tooltip"]').tooltip();
})
</script>

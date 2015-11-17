<div class="table-responsive">
    <div class="alert alert-info" role="alert"><span style="font-weight: bold">Pro Tip:</span> Use the check boxes to keep track of people you've already matched.</div>

    <h4>Continuing Team Members</h4>
    @if (!$teamMemberSummary['new'] && !$teamMemberSummary['missing'])
        <p>All team members transferred correctly</p>
    @else
    <table id="teamMembersTable" class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th style="text-align: center">Team Year</th>
            <th style="text-align: center">Quarter</th>
            <th>Status</th>
            <th>Attention</th>
        </tr>
        </thead>
        <tbody>
            @foreach (['missing', 'new'] as $type)
                @if ($teamMemberSummary[$type] )
                    @foreach ($teamMemberSummary[$type] as $existingPair)
                        <?php $new = $existingPair[0]; ?>
                        <?php $old = $existingPair[1]; ?>
                        <?php $member = $type == 'new' ? $new : $old; ?>
                        @if ($member->quarterNumber != 1)
                        <tr>
                            <td><input type="checkbox" /></td>
                            <td>{{ $member->firstName }}</td>
                            <td>{{ $member->lastName }}</td>
                            <td style="text-align: center">{{ $member->teamYear }}</td>
                            <td style="text-align: center">Q{{ $member->quarterNumber }}</td>
                            <td>{{ $type }}</td>
                            <td><?php
                                if ($type == 'new') {
                                    if ($old) {
                                        echo 'Withdrawn last quarter: ' . $old->withdrawCode->display;
                                    }
                                }
                            ?></td>
                        </tr>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
    @endif
    <br/><br/>

    <h4>Q1 Incoming</h4>
    @if (!$teamMemberSummary['new'] && !$incomingSummary['missing'])
        <p>All Q1 incoming transferred</p>
    @else
        <table id="incomingTable" class="table table-condensed table-striped table-hover">
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th style="text-align: center">Team Year</th>
                <th style="text-align: center">Quarter</th>
                <th>Status</th>
                <th>Attention</th>
            </tr>
            </thead>
            <tbody>
            {{-- Missing Incoming --}}
            @foreach ($incomingSummary['missing'] as $existingPair)
                <?php $new = $existingPair[0]; ?>
                <?php $old = $existingPair[1]; ?>
                <?php $incoming = $old; ?>
                @if ($incoming->incomingQuarterId == $thisQuarter->id)
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>{{ $incoming->firstName }}</td>
                    <td>{{ $incoming->lastName }}</td>
                    <td style="text-align: center">{{ $incoming->teamYear }}</td>
                    <td style="text-align: center"></td>
                    <td>missing</td>
                    <td><?php
                        if ($incoming->incomingQuarterId == $thisQuarter->id) {
                            echo 'Was expected this quarter';
                        } else {
                            echo 'Was expected in a future quarter';
                        }
                    ?></td>
                </tr>
                @endif
            @endforeach
            {{-- Additional Team Members --}}
            @foreach ($teamMemberSummary['new'] as $existingPair)
                <?php $new = $existingPair[0]; ?>
                <?php $old = $existingPair[1]; ?>
                <?php $member = $new; ?>
                @if ($member->quarterNumber == 1)
                <tr>
                    <td><input type="checkbox" /></td>
                    <td>{{ $member->firstName }}</td>
                    <td>{{ $member->lastName }}</td>
                    <td style="text-align: center">{{ $member->teamYear }}</td>
                    <td style="text-align: center">Q{{ $member->quarterNumber }}</td>
                    <td>new</td>
                    <td><?php

                    ?></td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    @endif
    <br/><br/>

    <h4>New/Missing Incoming</h4>
    @if (!$incomingSummary['new'] && !$incomingSummary['missing'])
        <p>No new or missing applications.</p>
    @else
        <table id="incomingTable" class="table table-condensed table-striped table-hover">
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th style="text-align: center">Team Year</th>
                <th>Status</th>
                <th>Attention</th>
            </tr>
            </thead>
            <tbody>
            @foreach (['missing', 'new'] as $type)
                @if ($incomingSummary[$type])
                    @foreach ($incomingSummary[$type] as $existingPair)
                        <?php $new = $existingPair[0]; ?>
                        <?php $old = $existingPair[1]; ?>
                        <?php $incoming = $type == 'new' ? $new : $old; ?>

                        @if ($type == 'new' || $incoming->incomingQuarterId != $thisQuarter->id)
                        <tr>
                            <td><input type="checkbox" /></td>
                            <td>{{ $incoming->firstName }}</td>
                            <td>{{ $incoming->lastName }}</td>
                            <td style="text-align: center">{{ $incoming->teamYear }}</td>
                            <td>{{ $type }}</td>
                            <td><?php
                                if ($type == 'missing') {
                                    if ($incoming->incomingQuarterId == $thisQuarter->id) {
                                        echo 'Was expected this quarter';
                                    } else {
                                        echo 'Was expected in a future quarter';
                                    }
                                } else {
                                    if ($old) {
                                        echo 'Withdrawn last quarter: ' . $old->withdrawCode->display;
                                    } else if ($new->regDate->lt($thisQuarter->startWeekendDate)) {
                                        echo 'Registered before quarter (' . $new->regDate->format('M j, Y') . '), but was not on last quarter\'s final sheet';
                                    } else if ($new->teamYear == 2 && $new->regDate->diffInDays($thisQuarter->startWeekendDate) < 4) {
                                        echo 'Registered at the weekend.';
                                    }
                                }
                            ?></td>
                        </tr>
                        @endif
                    @endforeach
                @endif
            @endforeach
            </tbody>
        </table>
    @endif
    <br/><br/>

    <h4>Modified Incoming</h4>
    @if (!$incomingSummary['changed'])
        <p>All exiting incoming transferred correctly</p>
    @else
    <table id="ongoingTable" class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th style="text-align: center">Team Year</th>
            <th style="text-align: center">Reg Date</th>
            <th style="text-align: center">App Out Date</th>
            <th style="text-align: center">App In Date</th>
            <th style="text-align: center">Approve Date</th>
            <th style="text-align: center">Incoming Quarter</th>
            <th>Attention</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($incomingSummary['changed'] as $existingPair)
            <?php $new = $existingPair[0]; ?>
            <?php $old = $existingPair[1]; ?>
            <tr>
                <td><input type="checkbox" /></td>
                <td>{{ $new->firstName }}</td>
                <td>{{ $new->lastName }}</td>
                <td {!! $new->teamYear != $old->teamYear ? 'class="bg-warning" title="Was '.$old->teamYear.'"' : '' !!} style="text-align: center">
                    {{ $new->teamYear }}
                </td>
                <td {!! $new->regDate && $new->regDate->ne($old->regDate) ? 'class="bg-warning" title="Was '.$old->regDate->format('M j, Y').'"' : '' !!} style="text-align: center">
                    {{ $new->regDate ? $new->regDate->format('M j, Y') : '' }}
                </td>
                <td {!! $new->appOutDate && $new->appOutDate->ne($old->appOutDate) ? 'class="bg-warning" title="Was '.$old->appOutDate->format('M j, Y').'"' : '' !!} style="text-align: center">
                    {{ $new->appOutDate ? $new->appOutDate->format('M j, Y') : '' }}
                </td>
                <td {!! $new->appInDate && $new->appInDate->ne($old->appInDate) ? 'class="bg-warning" title="Was '.$old->appInDate->format('M j, Y').'"' : '' !!} style="text-align: center">
                    {{ $new->appInDate ? $new->appInDate->format('M j, Y') : '' }}
                </td>
                <td {!! $new->apprDate && $new->apprDate->ne($old->apprDate) ? 'class="bg-warning" title="Was '.$old->apprDate->format('M j, Y').'"' : '' !!} style="text-align: center">
                    {{ $new->apprDate ? $new->apprDate->format('M j, Y') : '' }}
                </td>
                <td {!! $new->incomingQuarterId != $old->incomingQuarterId ? 'class="bg-warning"' : '' !!} style="text-align: center">
                    {{ $new->incomingQuarterId == $thisQuarter->getNextQuarter()->id ? 'Next' : 'Future' }}
                </td>
                <td><?php
                    if ($new->incomingQuarterId != $old->incomingQuarterId) {
                        $expectedQuarter = $old->incomingQuarterId == $thisQuarter->id
                            ? 'this'
                            : 'in a future';
                        $newExpectedQuarter = $new->incomingQuarterId == $thisQuarter->getNextQuarter()->id
                            ? 'next'
                            : 'a future';
                        echo "Was expected {$expectedQuarter} quarter, now is expected {$newExpectedQuarter} quarter.";
                    }
                ?></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#teamMembersTable').dataTable({
            "paging":    false,
            "searching": false,
            "order": [[ 1, "asc" ]],
            "columnDefs": [ { "targets": 0, "orderable": false } ]
        });
        $('#incomingTable').dataTable({
            "paging":    false,
            "searching": false,
            "order": [[ 1, "asc" ]],
            "columnDefs": [ { "targets": 0, "orderable": false } ]
        });
        $('#ongoingTable').dataTable({
            "paging":    false,
            "searching": false,
            "order": [[ 1, "asc" ]],
            "columnDefs": [ { "targets": 0, "orderable": false } ]
        });
    });
</script>

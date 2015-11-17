<div class="table-responsive">
    <div class="alert alert-info" role="alert"><span style="font-weight: bold">Pro Tip:</span> Use the check boxes to keep track of people you've already matched.</div>

    <h4>Continuing Team Members</h4>
    <div class="tableContainer">
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
    </div>
    <br/><br/>

    <h4>Q1 Incoming</h4>
    <div class="tableContainer">
        <table id="q1Table" class="table table-condensed table-striped table-hover">
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
    </div>
    <br/><br/>

    <h4>New/Missing Incoming</h4>
    <div class="tableContainer">
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
    </div>
    <br/><br/>

    <h4>Modified Incoming</h4>
    <div class="tableContainer">
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
            <?php
                $new = $existingPair[0];
                $old = $existingPair[1];

                $attributes = [
                    'teamYear' => '',
                    'regDate' => '',
                    'appOutDate' => '',
                    'appInDate' => '',
                    'apprDate' => '',
                    'incomingQuarterId' => '',
                ];
                foreach (array_keys($attributes) as $field) {
                    if (strpos($field, 'Date') !== false) {
                        if (($new->$field && $old->$field && $new->$field->ne($old->$field))
                            || ($old->$field && !$new->$field)
                        ) {
                            $attributes[$field] = 'class="bg-warning" title="Was ' . $old->$field->format('M j, Y') . '"';
                        }
                    } else {
                        if ($new->$field != $old->$field) {
                            $attributes[$field] = 'class="bg-warning"';
                            if (!preg_match('/Id$/', $field)) {
                                $attributes[$field] .= ' title="Was ' . $old->$field . '"';
                            }
                        }
                    }
                }
            ?>
            <tr>
                <td><input type="checkbox" /></td>
                <td>{{ $new->firstName }}</td>
                <td>{{ $new->lastName }}</td>
                <td {!! $attributes['teamYear'] !!} style="text-align: center">
                    {{ $new->teamYear }}
                </td>
                <td {!! $attributes['regDate'] !!} style="text-align: center">
                    {{ $new->regDate ? $new->regDate->format('M j, Y') : '' }}
                </td>
                <td {!! $attributes['appOutDate'] !!} style="text-align: center">
                    {{ $new->appOutDate ? $new->appOutDate->format('M j, Y') : '' }}
                </td>
                <td {!! $attributes['appInDate'] !!} style="text-align: center">
                    {{ $new->appInDate ? $new->appInDate->format('M j, Y') : '' }}
                </td>
                <td {!! $attributes['apprDate'] !!} style="text-align: center">
                    {{ $new->apprDate ? $new->apprDate->format('M j, Y') : '' }}
                </td>
                <td {!! $attributes['incomingQuarterId'] !!} style="text-align: center">
                    {{ $new->incomingQuarterId == $thisQuarter->getNextQuarter()->id ? 'Next' : 'Future' }}
                </td>
                <td><?php
                    if ($new->incomingQuarterId != $old->incomingQuarterId) {
                        $expectedQuarter = $old->incomingQuarterId == $thisQuarter->id
                            ? 'this'
                            : 'in a future';
                        $newExpectedQuarter = $new->incomingQuarterId == $thisQuarter->getNextQuarter()->id
                            ? 'next'
                            : 'in a future';
                        echo "Was expected {$expectedQuarter} quarter, now is expected {$newExpectedQuarter} quarter.";
                    }
                ?></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        var tables = [
            {
                id: "teamMembersTable",
                emptyMessage: "All team members transferred correctly"
            },
            {
                id: "q1Table",
                emptyMessage: "All Q1 incoming transferred"
            },
            {
                id: "incomingTable",
                emptyMessage: "No new or missing applications"
            },
            {
                id: "ongoingTable",
                emptyMessage: "All existing incoming transferred correctly"
            }
        ];

        $.each(tables, function(index, table) {
            $('#' + table.id).dataTable({
                "paging":    false,
                "searching": false,
                "order": [[ 1, "asc" ]],
                "columnDefs": [ { "targets": 0, "orderable": false } ],
                fnDrawCallback: function (settings) {
                    if (settings.fnRecordsDisplay() == 0) {
                        $(this).closest("div.tableContainer").html("<p>" + table.emptyMessage + "</p>");
                    }
                }
            });
        });
    });
</script>

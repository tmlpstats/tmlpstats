<div class="table-responsive">
    <br/>
    <div class="alert alert-info" role="alert"><span style="font-weight: bold">Pro Tip:</span> Use the check boxes to keep track of people you've already matched.</div>

    <h4>Continuing Team Members</h4>
    <div class="tableContainer">
    <table id="teamMembersTable" class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th class="data-point">Team Year</th>
            <th class="data-point">Quarter</th>
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
                            <td class="data-point">{{ $member->teamYear }}</td>
                            <td class="data-point">Q{{ $member->quarterNumber }}</td>
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
                <th class="data-point">Team Year</th>
                <th class="data-point">Quarter</th>
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
                    <td class="data-point">{{ $incoming->teamYear }}</td>
                    <td class="data-point"></td>
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
                    <td class="data-point">{{ $member->teamYear }}</td>
                    <td class="data-point">Q{{ $member->quarterNumber }}</td>
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
                <th class="data-point">Team Year</th>
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
                            <td class="data-point">{{ $incoming->teamYear }}</td>
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
                                    } else if ($new->regDate->lt($thisQuarter->getQuarterStartDate($incoming->center))) {
                                        echo 'Registered before quarter (' . $new->regDate->format('M j, Y') . '), but was not on last quarter\'s final sheet';
                                    } else if ($new->teamYear == 2 && $new->regDate->diffInDays($thisQuarter->getQuarterStartDate($incoming->center)) < 4) {
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
            <th class="data-point">Team Year</th>
            <th class="data-point">Reg Date</th>
            <th class="data-point">App Out Date</th>
            <th class="data-point">App In Date</th>
            <th class="data-point">Approve Date</th>
            <th class="data-point">Incoming Quarter</th>
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
                            $attributes[$field] = 'class="bg-warning" title="Was ' . $old->$field->format(TmlpStats\Util::getLocaleDateFormat()) . '"';
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
                <td {!! $attributes['teamYear'] !!} class="data-point">
                    {{ $new->teamYear }}
                </td>
                <td {!! $attributes['regDate'] !!} class="data-point">
                    @if ($new->regDate)
                        @date($new->regDate)
                    @endif
                </td>
                <td {!! $attributes['appOutDate'] !!} class="data-point">
                    @if ($new->appOutDate)
                        @date($new->appOutDate)
                    @endif
                </td>
                <td {!! $attributes['appInDate'] !!} class="data-point">
                    @if ($new->appInDate)
                        @date($new->appInDate)
                    @endif
                </td>
                <td {!! $attributes['apprDate'] !!} class="data-point">
                    @if ($new->apprDate)
                        @date($new->apprDate)
                    @endif
                </td>
                <td {!! $attributes['incomingQuarterId'] !!} class="data-point">
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

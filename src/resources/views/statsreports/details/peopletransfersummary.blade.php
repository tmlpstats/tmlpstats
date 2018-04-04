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
            @foreach (['missing', 'new', 'changed'] as $type)
                @if ($teamMemberSummary[$type] )
                    @foreach ($teamMemberSummary[$type] as $existingPair)
                        <?php $new = $existingPair[0]; ?>
                        <?php $old = $existingPair[1]; ?>
                        <?php $member = $type !== 'missing' ? $new : $old; ?>
                        @if ($member->quarterNumber != 1)
                        <tr>
                            <td><input type="checkbox" /></td>
                            <td>{{ $member->firstName }}</td>
                            <td>{{ $member->lastName }}</td>
                            <td class="data-point">{{ $member->teamYear }}</td>
                            <td class="data-point">Q{{ $member->quarterNumber }}</td>
                            <td>{{ $type }}</td>
                            <td {!! ($type == 'changed') ? 'class="bg-warning"' : '' !!}><?php
                                if ($type == 'new' && $old && $old->withdrawCode) {
                                    echo 'Withdrawn last quarter: ' . $old->withdrawCode->display;
                                } else if ($type == 'changed' && $new->incomingQuarter->id != $old->incomingQuarter->id) {
                                    // Quarter number is calculated based on the current report date. That means when we get the quarter number
                                    // for a person from the previous quarter, we need to manually adjust the value to be accurate.
                                    $teamQuarterLastWeek = $old->quarterNumber - 1;
                                    echo "Was listed as Q{$teamQuarterLastWeek} last quarter, but is Q{$new->quarterNumber} this quarter. Were they added to the correct section on the class list?";
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
                                        echo 'Registered before quarter (' . $new->regDate->format('M j, Y') . '), but was not on last quarter\'s final report';
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

                $classes = $oldValues = [
                    'teamYear' => '',
                    'regDate' => '',
                    'appOutDate' => '',
                    'appInDate' => '',
                    'apprDate' => '',
                    'incomingQuarterId' => '',
                ];
                foreach (array_keys($classes) as $field) {
                    if (strpos($field, 'Date') !== false) {
                        if (($new->$field && $old->$field && $new->$field->ne($old->$field))
                            || ($old->$field && !$new->$field)
                        ) {
                            $classes[$field] = 'bg-warning';
                            $oldValues[$field] = $old->$field;
                        }
                    } else {
                        if ($new->$field != $old->$field) {
                            $classes[$field] = 'bg-warning';
                            $oldValues[$field] = $old->$field;
                        }
                    }
                }
            ?>
            <tr>
                <td><input type="checkbox" /></td>
                <td>{{ $new->firstName }}</td>
                <td>{{ $new->lastName }}</td>
                <td class="data-point {{ $classes['teamYear'] }}">
                    {{ $new->teamYear }}
                </td>
                <td class="data-point {{ $classes['regDate'] }}">
                    @if ($new->regDate)
                        @date($new->regDate)
                        @if ($oldValues['regDate'])
                            was @date($oldValues['regDate'])
                        @endif
                    @endif
                </td>
                <td class="data-point {{ $classes['appOutDate'] }}">
                    @if ($new->appOutDate)
                        @date($new->appOutDate)
                        @if ($oldValues['appOutDate'])
                            was @date($oldValues['appOutDate'])
                        @endif
                    @endif
                </td>
                <td class="data-point {{ $classes['appInDate'] }}">
                    @if ($new->appInDate)
                        @date($new->appInDate)
                        @if ($oldValues['appInDate'])
                            was @date($oldValues['appInDate'])
                        @endif
                    @endif
                </td>
                <td class="data-point {{ $classes['apprDate'] }}">
                    @if ($new->apprDate)
                        @date($new->apprDate)
                        @if ($oldValues['apprDate'])
                            was @date($oldValues['apprDate'])
                        @endif
                    @endif
                </td>
                <td class="data-point {{ $classes['incomingQuarterId'] }}">
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
                        if ($expectedQuarter == $newExpectedQuarter) {
                            echo "Last quarter {$new->firstName} was listed as incoming in a future quarter. They are still listed as incoming in a future quarter. Please confirm this is correct.";
                        } else {
                            echo "Was expected {$expectedQuarter} quarter, now is expected {$newExpectedQuarter} quarter.";
                        }
                    }
                ?></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>

<!-- SCRIPTS_FOLLOW -->
<script type="text/javascript">
    $(document).ready(function() {

        var tables = [
            {
                id: 'teamMembersTable',
                emptyMessage: 'All team members transferred correctly'
            },
            {
                id: 'q1Table',
                emptyMessage: 'All Q1 incoming transferred'
            },
            {
                id: 'incomingTable',
                emptyMessage: 'No new or missing applications'
            },
            {
                id: 'ongoingTable',
                emptyMessage: 'All existing incoming transferred correctly'
            }
        ]

        $.each(tables, function(index, table) {
            $('#' + table.id).dataTable({
                'paging':    false,
                'searching': false,
                'order': [[ 1, 'asc' ]],
                'columnDefs': [ { 'targets': 0, 'orderable': false } ],
                fnDrawCallback: function (settings) {
                    if (settings.fnRecordsDisplay() == 0) {
                        $(this).closest('div.tableContainer').html('<p>' + table.emptyMessage + '</p>')
                    }
                }
            })
        })
    })
</script>

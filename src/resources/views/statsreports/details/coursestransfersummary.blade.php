<div class="table-responsive">
    @if ($flaggedCount == 0)
        <br/>
        <p>All courses copied correctly.</p>
    @else
        @foreach (['new', 'missing', 'changed'] as $status)
            @foreach (['CAP', 'CPC'] as $type)
                @if (isset($flagged[$status][$type]))
                    <br/>
                    <table class="table table-condensed table-striped">
                        <thead>
                        <tr>
                            <th colspan="12">{{ ucwords($type) }}</th>
                        </tr>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th colspan="3" class="data-point border-left">Quarter Starting</th>
                            <th colspan="3" class="data-point border-left">Current</th>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th class="data-point border-left">Total Ever Registered</th>
                            <th class="data-point">Standard Starts</th>
                            <th class="data-point">Transferred in from Previous</th>
                            <th class="data-point border-left">Total Ever Registered</th>
                            <th class="data-point">Standard Starts</th>
                            <th class="data-point">Transferred in from Previous</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($flagged[$status][$type] as $courseData)
                            <?php
                            $new = $courseData[0];
                            $old = $courseData[1];

                            $course = $status == 'changed' || $status == 'new'
                                ? $new
                                : $old;

                            $mappings = [
                                'quarterStartTer'            => 'currentTer',
                                'quarterStartStandardStarts' => 'currentStandardStarts',
                                'quarterStartXfer'           => 'currentXfer',
                            ];
                            $attributes = [
                                'quarterStartTer'            => '',
                                'quarterStartStandardStarts' => '',
                                'quarterStartXfer'           => '',
                            ];
                            if ($status == 'changed') {
                                foreach (array_keys($mappings) as $field) {
                                    if ($new[$field] != $old[$mappings[$field]]) {
                                        $attributes[$field] = 'class="bg-warning"';
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td>{{ $status }}</td>
                                <td>{{ $course['location'] }}</td>
                                <td class="data-point">@date($course['startDate'])</td>
                                <td {!! $attributes['quarterStartTer'] !!} class="data-point border-left">
                                    {{ $course['quarterStartTer'] }}
                                    @if ($attributes['quarterStartTer'])
                                        was {{ $old['currentTer'] }}
                                    @endif
                                </td>
                                <td {!! $attributes['quarterStartStandardStarts'] !!} class="data-point">
                                    {{ $course['quarterStartStandardStarts'] }}
                                    @if ($attributes['quarterStartStandardStarts'])
                                        was {{ $old['currentStandardStarts'] }}
                                    @endif
                                </td>
                                <td {!! $attributes['quarterStartXfer'] !!} class="data-point">
                                    {{ $course['quarterStartXfer'] }}
                                    @if ($attributes['quarterStartXfer'])
                                        was {{ $old['currentXfer'] }}
                                    @endif
                                </td>
                                <td class="data-point border-left">{{ $course['currentTer'] }}</td>
                                <td class="data-point">{{ $course['currentStandardStarts'] }}</td>
                                <td class="data-point">{{ $course['currentXfer'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
        @endforeach
    @endif
</div>

<div class="table-responsive">
    @if (!$flagged)
        <br/>
        <p>All courses copied correctly.</p>
    @else
        @foreach (['CAP', 'CPC'] as $type)
            @if (isset($flagged[$type]))
                <br/>
                <table class="table table-condensed table-striped">
                    <thead>
                    <tr>
                        <th colspan="12">{{ ucwords($type) }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th colspan="3" class="data-point border-left">Quarter Starting</th>
                        <th colspan="3" class="data-point border-left">Current</th>
                    </tr>
                    <tr>
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
                    @foreach ($flagged[$type] as $courseData)
                        <?php
                        $new = $courseData[0];
                        $old = $courseData[1];

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
                        foreach (array_keys($mappings) as $field) {
                            if ($new[$field] != $old[$mappings[$field]]) {
                                $attributes[$field] = 'class="bg-warning"';
                            }
                        }
                        ?>
                        <tr>
                            <td>{{ $new['location'] }}</td>
                            <td class="data-point">@date($new['startDate'])</td>
                            <td {!! $attributes['quarterStartTer'] !!} class="data-point border-left">{{ $new['quarterStartTer'] }}
                                {{ $attributes['quarterStartTer'] ? 'was ' . $old['currentTer'] : '' }}</td>
                            <td {!! $attributes['quarterStartStandardStarts'] !!} class="data-point">{{ $new['quarterStartStandardStarts'] }}
                                {{ $attributes['quarterStartStandardStarts'] ? 'was ' . $old['currentStandardStarts'] : '' }}</td>
                            <td {!! $attributes['quarterStartXfer'] !!} class="data-point">{{ $new['quarterStartXfer'] }}
                                {{ $attributes['quarterStartXfer'] ? 'was ' . $old['currentXfer'] : '' }}</td>
                            <td class="data-point border-left">{{ $new['currentTer'] }}</td>
                            <td class="data-point">{{ $new['currentStandardStarts'] }}</td>
                            <td class="data-point">{{ $new['currentXfer'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @endif
</div>

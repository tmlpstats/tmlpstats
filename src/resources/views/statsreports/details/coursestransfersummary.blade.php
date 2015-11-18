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
                        <th style="border-right: 2px solid #DDD;">&nbsp;</th>
                        <th colspan="3" style="border-right: 2px solid #DDD; text-align: center">Quarter Starting</th>
                        <th colspan="3" style="text-align: center">Current</th>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <th style="border-right: 2px solid #DDD;">Date</th>
                        <th style="text-align: center">Total Ever Registered</th>
                        <th style="text-align: center">Standard Starts</th>
                        <th style="border-right: 2px solid #DDD; text-align: center">Transferred in from Previous</th>
                        <th style="text-align: center">Total Ever Registered</th>
                        <th style="text-align: center">Standard Starts</th>
                        <th style="text-align: center">Transferred in from Previous</th>
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
                            <td style="vertical-align: middle; border-right: 2px solid #DDD;">{{ $new['startDate']->format("M j, Y") }}</td>
                            <td {!! $attributes['quarterStartTer'] !!} style="vertical-align: middle; text-align: center">{{ $new['quarterStartTer'] }}
                                {{ $attributes['quarterStartTer'] ? 'was ' . $old['currentTer'] : '' }}</td>
                            <td {!! $attributes['quarterStartStandardStarts'] !!} style="vertical-align: middle; text-align: center">{{ $new['quarterStartStandardStarts'] }}
                                {{ $attributes['quarterStartStandardStarts'] ? 'was ' . $old['currentStandardStarts'] : '' }}</td>
                            <td {!! $attributes['quarterStartXfer'] !!} style="vertical-align: middle; border-right: 2px solid #DDD; text-align: center">{{ $new['quarterStartXfer'] }}
                                {{ $attributes['quarterStartXfer'] ? 'was ' . $old['currentXfer'] : '' }}</td>
                            <td style="vertical-align: middle; text-align: center">{{ $new['currentTer'] }}</td>
                            <td style="vertical-align: middle; text-align: center">{{ $new['currentStandardStarts'] }}</td>
                            <td style="vertical-align: middle; text-align: center">{{ $new['currentXfer'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @endif
</div>

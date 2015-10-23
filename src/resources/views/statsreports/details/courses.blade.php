<div class="table-responsive">
    @foreach ($courses as $type => $group)
        <?php
            if (!in_array($type, ['CAP', 'CPC'])) {
                continue;
            }
        ?>
        <table class="table table-condensed table-striped table-hover">
            <thead>
            <tr>
                <th colspan="12">{{ ucwords($type) }}</th>
            </tr>
            <tr>
                <th>Location</th>
                <th style="border-right: 2px solid #DDD;">Date</th>
                <th>Quarter Start TER</th>
                <th>Quarter Start SS</th>
                <th style="border-right: 2px solid #DDD;">Quarter Start Xfer</th>
                <th>Current TER</th>
                <th>Current SS</th>
                <th style="border-right: 2px solid #DDD;">Current Xfer</th>
                <th>Completed SS</th>
                <th>Potentials</th>
                <th>Registrations</th>
            </tr>
            </thead>
            <tbody>
                @foreach($group as $courseData)
                    <tr>
                        <td>{{ $courseData->course->location ?: $courseData->course->center->name }}</td>
                        <td style="border-right: 2px solid #DDD;">{{ $courseData->course->startDate->format("M j, Y") }}</td>
                        <td style="text-align: center">{{ $courseData->quarterStartTer }}</td>
                        <td style="text-align: center">{{ $courseData->quarterStartStandardStarts }}</td>
                        <td style="border-right: 2px solid #DDD; text-align: center">{{ $courseData->quarterStartXfer }}</td>
                        <td style="text-align: center">{{ $courseData->currentTer }}</td>
                        <td style="text-align: center">{{ $courseData->currentStandardStarts }}</td>
                        <td style="border-right: 2px solid #DDD; text-align: center">{{ $courseData->currentXfer }}</td>
                        <td style="text-align: center">{{ $courseData->completedStandardStarts }}</td>
                        <td style="text-align: center">{{ $courseData->potentials }}</td>
                        <td style="text-align: center">{{ $courseData->registrations }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>

<div class="table-responsive">
    @foreach ($courses as $type => $group)
        <table class="table table-condensed table-striped">
            <thead>
            <tr>
                <th colspan="12">{{ ucwords($type) }}</th>
            </tr>
            <tr>
                <th>Location</th>
                <th>Date</th>
                <th>Quarter Start TER</th>
                <th>Quarter Start SS</th>
                <th>Quarter Start Xfer</th>
                <th>Current TER</th>
                <th>Current SS</th>
                <th>Current Xfer</th>
                <th>Completed SS</th>
                <th>Potentials</th>
                <th>Registrations</th>
            </tr>
            </thead>
            <tbody>
                @foreach($group as $courseData)
                    <tr>
                        <td>{{ $courseData->course->center->name }}</td>
                        <td>{{ $courseData->course->startDate->format('m/j/y') }}</td>
                        <td style="text-align: center">{{ $courseData->quarterStartTer }}</td>
                        <td style="text-align: center">{{ $courseData->quarterStartStandardStarts }}</td>
                        <td style="text-align: center">{{ $courseData->quarterStartXfer }}</td>
                        <td style="text-align: center">{{ $courseData->currentTer }}</td>
                        <td style="text-align: center">{{ $courseData->currentStandardStarts }}</td>
                        <td style="text-align: center">{{ $courseData->currentXfer }}</td>
                        <td style="text-align: center">{{ $courseData->completedStandardStarts }}</td>
                        <td style="text-align: center">{{ $courseData->potentials }}</td>
                        <td style="text-align: center">{{ $courseData->registrations }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>

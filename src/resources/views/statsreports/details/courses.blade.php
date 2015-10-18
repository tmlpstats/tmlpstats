<div class="table-responsive">
    <h3>Courses</h3>
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
                        <td>{{ $courseData->quarterStartTer }}</td>
                        <td>{{ $courseData->quarterStartStandardStarts }}</td>
                        <td>{{ $courseData->quarterStartXfer }}</td>
                        <td>{{ $courseData->currentTer }}</td>
                        <td>{{ $courseData->currentStandardStarts }}</td>
                        <td>{{ $courseData->currentXfer }}</td>
                        <td>{{ $courseData->completedStandardStarts }}</td>
                        <td>{{ $courseData->potentials }}</td>
                        <td>{{ $courseData->registrations }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>

<div class="table-responsive">
    <br />
    @if ($type == 'guests')
        @if (count($reportData) > 0)
            @include('reports.courses.guests', ['coursesData' => $reportData])
        @else
            <p>No courses currently have guest games.</p>
        @endif
    @elseif ($type == 'completed')
        @if (count($reportData) > 0)
            @include('reports.courses.completed', ['coursesData' => $reportData, 'excludeGuestGame' => true])
        @else
            <p>No completed courses.</p>
        @endif
    @else
        @forelse ($reportData as $type => $coursesData)
            <h4>{{ $type }}</h4>
            @include('reports.courses.upcoming', ['coursesData' => $coursesData, 'excludeGuestGame' => true])
            <br />
        @empty
            <p>No upcoming courses.</p>
        @endforelse
    @endif
</div>

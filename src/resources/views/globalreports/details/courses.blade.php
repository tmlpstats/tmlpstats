<div class="table-responsive">
    <br />
    @if ($type == 'summary')
        @forelse ($reportData as $courseType => $coursesData)
            <h4>{{ $courseType }}</h4>
            @include('reports.courses.summary', compact('coursesData'))
            <br />
        @empty
            <p>No courses available.</p>
        @endforelse
    @elseif ($type == 'guests')
        @if (count($reportData) > 0)
            @include('reports.courses.guests', ['coursesData' => $reportData])
        @else
            <p>No courses currently have transforming lives games.</p>
        @endif
    @elseif ($type == 'completedThisWeek')
        @if (count($reportData) > 0)
            @include('reports.courses.completed', ['coursesData' => $reportData, 'excludeGuestGame' => true])
        @else
            <p>No completed courses.</p>
        @endif
    @elseif ($type == 'completed')
        @forelse ($reportData as $courseType => $coursesData)
            <h4>{{ $courseType }}</h4>
            @include('reports.courses.completed', ['coursesData' => $coursesData, 'excludeGuestGame' => true])
            <br />
        @empty
            <p>No completed courses.</p>
        @endforelse
    @else
        @forelse ($reportData as $courseType => $coursesData)
            <h4>{{ $courseType }}</h4>
            @if ($type == 'next5weeks')
                @include('reports.courses.next5weeks', ['coursesData' => $coursesData, 'excludeGuestGame' => true])
            @else
                @include('reports.courses.upcoming', ['coursesData' => $coursesData, 'excludeGuestGame' => true])
            @endif
            <br />
        @empty
            <p>No upcoming courses.</p>
        @endforelse
    @endif
</div>

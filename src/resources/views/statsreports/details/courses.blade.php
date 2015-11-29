<div class="table-responsive">
    @foreach (['CAP', 'CPC'] as $type)
        @if (isset($reportData[$type]))
            <br/>
            <h4>{{ $type }}</h4>
            @include('reports.courses.upcoming', ['coursesData' => $reportData[$type]])
        @endif
    @endforeach

    @if (isset($reportData['completed']))
        <br/>
        <h4>Completed</h4>
        @include('reports.courses.completed', ['coursesData' => $reportData['completed']])
    @endif
</div>

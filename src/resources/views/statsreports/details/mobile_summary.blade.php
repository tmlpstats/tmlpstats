@extends('template')

@section('content')
    <h2>{{ $statsReport->center->name }}</h2>
    <div class="row">
        <div class="col-xs-12">
            <p class="bg-info">This is a test for our foray into mobile device support. Please email us and let us know if there are any problems on your specific device; and also let us know if there's a piece of information you'd like to see here!
        </div>
    </div>

    <div class="row">
        <div class="col-sm-11">
            @include('reports.centergames.week', compact('reportData'))
            Updated {{ $statsReport->submittedAt }}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            @if ($completedCourses)
                <h4>Course Results:</h4>
                <!--<dl class="dl-horizontal">-->
                    @foreach ($completedCourses as $courseData)
                        <span style="text-decoration: underline">{{ $courseData['type'] }}
                            - {{ $courseData['startDate']->format('M j') }}</span>
                        <dl class="dl-horizontal">
                            <dt>Standard Starts:</dt>
                            <dd>{{ $courseData['currentStandardStarts'] }}</dd>
                            <dt>Reg Fulfillment:</dt>
                            <dd>{{ $courseData['completionStats']['registrationFulfillment'] }}%</dd>
                            <dt>Reg Effectiveness:</dt>
                            <dd>{{ $courseData['completionStats']['registrationEffectiveness'] }}%</dd>
                            <dt>Registrations:</dt>
                            <dd>{{ $courseData['registrations'] }}</dd>
                        </dl>
                    @endforeach
                <!--</dl>-->
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('/js/api.js') }}" type="text/javascript"></script>
@endsection

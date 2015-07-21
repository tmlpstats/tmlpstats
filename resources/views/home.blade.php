@extends('template')

@section('content')
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (Auth::user()->hasRole('localStatistician') || Auth::user()->hasRole('globalStatistician'))
                        <h1>Results for Week Ending {{ $reportingDate->format('F j, Y') }}</h1>

                        {!! Form::open(['url' => 'home', 'class' => 'form-horizontal']) !!}
                        <div class="form-group">
                            {!! Form::label('stats_report', 'Week:', ['class' => 'col-sm-1 control-label']) !!}
                            <div class="col-sm-2">
                                {!! Form::select('stats_report', $reportingDates, $reportingDate->toDateString(), ['class' => 'form-control',  'onchange' => 'this.form.submit()']) !!}
                            </div>
                        </div>

                        @include('partials.regions')
                        {!! Form::close() !!}

                        @foreach ($regionsData as $data)
                        <h3>{{ $data['displayName'] }}</h3>
                        <h4>({{ $data['completeCount'] }} of {{ $data['validatedCount'] }} centers complete)</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Center</th>
                                    <th>Region</th>
                                    <th style="text-align: center">Submitted</th>
                                    <th>Rating</th>
                                    <th>Last Submitted</th>
                                    <th>Last Submitted By</th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($data['centersData'] as $name => $center)
                                    <tr class="{{ $center['validated'] ? 'success' : 'danger' }}" >
                                    <td>{{ $center['name'] }}</td>
                                    <td>{{ $center['localRegion'] }}</td>
                                    <td style="text-align: center"><span class="glyphicon {{ $center['submitted'] ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
                                    <td>{{ $center['rating'] }}</td>
                                    <td>{{ $center['updatedAt'] }}</td>
                                    <td>{{ $center['updatedBy'] }}</td>
                                    <td style="text-align: center">
                                    @if ($center['sheet'])
                                        <a href="{{ $center['sheet'] }}" title="Download" style="color: black"><span class="glyphicon glyphicon-cloud-download"></span></a>
                                    @endif
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endforeach
                    @else
                        Welcome to your dashboard. This will be the home of your TMLP stats experience. We're still in the process of creating it, but check back soon for some great new features.
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        if ("<?php echo $timezone; ?>".length == 0) {
            var tz = jstz.determine();

            if (typeof (tz) !== 'undefined') {

                var timezone = tz.name();

                $.ajax({
                    type: "POST",
                    url: "{{ action('HomeController@setTimezone') }}",
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                    },
                    data: 'timezone=' + encodeURI(tz.name()),
                    success: function() {
                        location.reload();
                    }
                });
            }
        }
    });
</script>
@endsection

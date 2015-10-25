@extends('template')

@section('content')
    @if (!Auth::user()->hasRole('localStatistician') && !Auth::user()->hasRole('globalStatistician') && !Auth::user()->hasRole('administrator'))
        Welcome to your dashboard. If you think you should have access to this site, please contact your
        regional statistician.
    @else
        <h1>Results for Week Ending {{ $reportingDate->format('F j, Y') }}</h1>

        <div class="table-responsive" style="overflow: hidden">
            {!! Form::open(['url' => 'home']) !!}
            <div class="row">
                <div class="col-md-3" style="align-content: center">
                    {!! Form::label('region', 'Other Regions:', ['class' => 'control-label']) !!}
                </div>
                <div class="col-md-3" style="align-content: center">
                    {!! Form::label('reportingDate', 'Other Weeks:', ['class' => 'control-label']) !!}
                </div>
                <div class="col-md-6"></div>
            </div>
            <div class="row">
                <div class="col-md-3" style="align-content: center">
                    @include('partials.forms.regions')
                </div>
                <div class="col-md-3" style="align-content: center">
                    {!! Form::select('reportingDate', $reportingDates, $reportingDate->toDateString(), ['class' => 'form-control',  'onchange' => 'this.form.submit()']) !!}
                </div>
                <div class="col-md-6"></div>
            </div>
            {!! Form::close() !!}
        </div>

        @foreach ($regionsData as $data)
            <h3>{{ $data['displayName'] }}</h3>
            <h4>({{ $data['completeCount'] }} of {{ $data['validatedCount'] }} centers are complete)</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Center</th>
                        <th style="text-align: center">Region</th>
                        <th style="text-align: center">Submitted</th>
                        <th>Rating</th>
                        <th>Submitted</th>
                        <th>Submitted By</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['centersData'] as $name => $center)
                        <tr class="{{ $center['validated'] ? 'success' : 'danger' }}">
                            <td>{{ $center['name'] }}</td>
                            <td style="text-align: center">{{ $center['localRegion'] ?: '' }}</td>
                            <td style="text-align: center">
                                <span
                                    class="glyphicon {{ $center['submitted'] ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span>
                            </td>
                            <td>{{ $center['rating'] }}</td>
                            <td>{{ $center['updatedAt'] }}</td>
                            <td>{{ $center['updatedBy'] }}</td>
                            <td style="text-align: center">
                                @if ($center['reportUrl'])
                                    <a href="{{ $center['reportUrl'] }}" class="view" title="View" style="color: black">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                @endif
                            </td>
                            <td style="text-align: center">
                                @if ($center['sheet'])
                                    <a href="{{ $center['sheet'] }}" title="Download" style="color: black">
                                        <span class="glyphicon glyphicon-cloud-download"></span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
@endsection

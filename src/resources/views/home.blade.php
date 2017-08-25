@extends('template')

@section('content')
    @cannot ('index', TmlpStats\StatsReport::class)
    <h1>Welcome {{ Auth::user()->firstName }}</h1>
    <p>It looks like your account isn't completely setup. Please contact <strong>future.tmlpstat@gmail.com</strong> and they will make sure you get access to everything you need.</p>
    @else
        <h1>Results for Week Ending {{ $reportingDate->format('F j, Y') }}</h1>
        <div id="react-routed-flow"></div>

        @foreach ($regionsData as $data)
            <h3>{{ $data['displayName'] }}</h3>
            <h4>({{ $data['completeCount'] }} of {{ $data['validatedCount'] }} centers are complete)</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Center</th>
                        <th style="text-align: center">Submitted</th>
                        <th>Rating</th>
                        <th>Submitted</th>
                        <th>Submitted By</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['centersData'] as $name => $center)
                        <tr class="{{ $center['validated'] ? 'success' : 'danger' }}">
                            <td>
                                @if ($center['reportUrl'])
                                    <a href="{{ $center['reportUrl'] }}">
                                        {{ $center['name'] }}
                                    </a>
                                @else
                                    {{ $center['name'] }}
                                @endif
                            </td>
                            <td style="text-align: center">
                                <span class="glyphicon {{ $center['submitted'] ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span>
                            </td>
                            <td>{{ $center['rating'] }}</td>
                            <td>{{ $center['updatedAt'] }}</td>
                            <td>{{ $center['updatedBy'] }}</td>
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
    @endcan
@endsection

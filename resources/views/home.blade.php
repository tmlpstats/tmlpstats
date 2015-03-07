@extends('template')

@section('content')
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Dashboard</div>

				<div class="panel-body">
					@if (Auth::user()->hasRole('globalStatistician'))
						<h1>Results for Week Ending {{ $reportingDate }}</h1>
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
								<tr>
									<th data-sortable="true">Center</th>
									<th data-sortable="true">Region</th>
									<th data-sortable="true">Complete</th>
									<th data-sortable="true">Rating</th>
									<th data-sortable="true">Last Submitted</th>
									<th data-sortable="true">Last Submitted By</th>
								</tr>
								</thead>
								<tbody>
								@foreach ($centersData as $center)
								<tr {!! !$center['validated'] ? 'style="background-color: MistyRose"' : 'style="background-color: #E0FEE0"' !!}>
									<td>{{ $center['name'] }}</td>
									<td>{{ $center['localRegion'] }}</td>
									<td><span style="align: center" class="glyphicon {{ $center['validated'] ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
									<td>{{ $center['rating'] }}</td>
									<td>{{ $center['updatedAt'] }}</td>
									<td>{{ $center['updatedBy'] }}</td>
								</tr>
								@endforeach
								</tbody>
							</table>
						</div>
					@else
						Welcome to your dashboard. This will be the home of your TMLP stats experience. We're still in the process of creating it, but check back soon for some great new features.
					@endif
				</div>
			</div>
		</div>
	</div>
@endsection

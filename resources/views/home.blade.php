@extends('template')

@section('content')
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Dashboard</div>

				<div class="panel-body">
					@if (Auth::user()->hasRole('globalStatistician'))
						<h1>Results for Week Ending {{ $reportingDate->format('F j, Y') }}</h1>

						{!! Form::open(['url' => 'home', 'class' => 'form-horizontal']) !!}
						<div class="form-group">
							{!! Form::label('stats_report', 'Week:', ['class' => 'col-sm-1 control-label']) !!}
							<div class="col-sm-2">
								{!! Form::select('stats_report', $reportingDates, $reportingDate->toDateString(), ['class' => 'form-control',  'onchange' => 'this.form.submit()']) !!}
							</div>
						</div>
						{!! Form::close() !!}
						<h3>Eastern Region</h3>
						<h4>({{ $results['eastComplete'] }} of {{ $results['eastCount'] }} centers complete)</h4>
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
								@if ($center['localRegion'] == 'East')
								<tr {!! !$center['validated'] ? 'style="background-color: MistyRose"' : 'style="background-color: #E0FEE0"' !!}>
									<td>{{ $center['name'] }}</td>
									<td>{{ $center['localRegion'] }}</td>
									<td><span style="align: center" class="glyphicon {{ $center['validated'] ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
									<td>{{ $center['rating'] }}</td>
									<td>{{ $center['updatedAt'] }}</td>
									<td>{{ $center['updatedBy'] }}</td>
								</tr>
								@endif
								@endforeach
								</tbody>
							</table>
						</div>
						<h3>Western Region</h3>
						<h4>({{ $results['westComplete'] }} of {{ $results['westCount'] }} centers complete)</h4>
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
								@if ($center['localRegion'] == 'West')
								<tr {!! !$center['validated'] ? 'style="background-color: MistyRose"' : 'style="background-color: #E0FEE0"' !!}>
									<td>{{ $center['name'] }}</td>
									<td>{{ $center['localRegion'] }}</td>
									<td><span style="align: center" class="glyphicon {{ $center['validated'] ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
									<td>{{ $center['rating'] }}</td>
									<td>{{ $center['updatedAt'] }}</td>
									<td>{{ $center['updatedBy'] }}</td>
								</tr>
								@endif
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

@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		if("<?php echo $timezone; ?>".length == 0){
			var tz = jstz.determine();

			if (typeof (tz) !== 'undefined') {

				var timezone = tz.name();

				console.log(tz);
				console.log(timezone);

				$.ajax({
					type: "GET",
					url: "{{ action('HomeController@setTimezone') }}",
					data: 'timezone=' + encodeURI(tz.name()),
					success: function(){
						location.reload();
					}
				});
			}
		}
	});
</script>
@endsection
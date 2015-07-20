@extends('template')

@section('content')
<h1>Dashboard</h1>
<ul>
    <li><a href="{{ url('/admin/centers') }}">View Centers</a></li>
    <li><a href="{{ url('/admin/quarters') }}">View Quarters</a></li>
    <li><a href="{{ url('/admin/users') }}">View Users</a></li>
    <li><a href="{{ url('/admin/statsreports') }}">View Stats Reports</a></li>
    <li><a href="{{ url('/admin/globalreports') }}">View Global Reports</a></li>
    {{-- <li><a href="{{ url('/admin/import') }}">Import Previous Quarter Stats Sheets</a></li> --}}
</ul>

<br/>
<div>
    <h2>Results for Week Ending {{ $reportingDate->format('F j, Y') }}</h2>

    {!! Form::open(['url' => 'admin/dashboard', 'class' => 'form-horizontal']) !!}
    <div class="form-group">
        {!! Form::label('stats_report', 'Week:', ['class' => 'col-sm-1 control-label']) !!}
        <div class="col-sm-2">
            {!! Form::select('stats_report', $reportingDates, $reportingDate->toDateString(), ['class' => 'form-control',  'onchange' => 'this.form.submit()']) !!}
        </div>
    </div>

    @include('partials.regions')
    {!! Form::close() !!}

    <div id="errors" class="alert alert-danger" role="alert" style="display:none">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <span class="message-prefix" style="font-weight:bold">Error: </span>
        <span class="message"></span>
    </div>

    @foreach ($regionsData as $data)
    <h3>{{ $data['displayName'] }}</h3>
    <h4>({{ $data['completeCount'] }} of {{ $data['validatedCount'] }} centers complete)</h4>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Center</th>
                <th>Region</th>
                <th style="text-align: center">Complete</th>
                <th>Rating</th>
                <th>Submitted At</th>
                <th>Submitted By</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data['centersData'] as $name => $center)
            <tr id="{{ $center['statsReportId'] }}" class="{!! $center['complete'] ? 'success' : 'danger' !!}" >
                <td>{{ $center['name'] }}</td>
                <td>{{ $center['localRegion'] }}</td>
                <td style="text-align: center">
                    <span class="glyphicon {{ $center['complete'] ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span>
                </td>
                <td>{{ $center['rating'] }}</td>
                <td>{{ $center['updatedAt'] }}</td>
                <td>{{ $center['updatedBy'] }}</td>
                <td style="text-align: center">
                    @if ($center['statsReportId'])
                    <a href="#" class="lock" title="Lock" style="color: black">
                        <i class="fa {{ $center['locked'] ? 'fa-lock' : 'fa-unlock' }}"></i>
                    </a>
                    @endif
                </td>
                <td style="text-align: center">
                    @if ($center['statsReportId'])
                    <a href="{{ url('/admin/statsreports/' . $center['statsReportId']) }}" class="view" title="View" style="color: black">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                    @endif
                </td>
                <td style="text-align: center">
                @if ($center['sheet'])
                    <a href="{{ $center['sheet'] }}" class="download" title="Download" style="color: black">
                        <span class="glyphicon glyphicon-cloud-download"></span>
                    </a>
                @endif
                </td>
                <td style="text-align: center">
                    @if ($center['statsReportId'])
                    &nbsp;
                    &nbsp;
                    <a href="#" class="delete" title="Delete" style="color: black">
                        <span class="glyphicon glyphicon-trash"></span>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $("a.lock").click(function() {
            $.ajax({
                type: "PATCH",
                url: "statsreports/" + $(this).closest('tr').attr('id'),
                data: "locked=" + $(this).find("i.fa").hasClass("fa-unlock"), // if it is currently unlocked, lock it,
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                success: function(response) {
                    if (response.success) {
                        $("#errors").hide();

                        if (response.locked) {
                            $("#" + response.statsReport + " a.lock i.fa").removeClass("fa-unlock");
                            $("#" + response.statsReport + " a.lock i.fa").addClass("fa-lock");
                        } else {
                            $("#" + response.statsReport + " a.lock i.fa").removeClass("fa-lock");
                            $("#" + response.statsReport + " a.lock i.fa").addClass("fa-unlock");
                        }
                    } else {
                        $("#errors").removeClass("alert-success");
                        $("#errors").addClass("alert-danger");
                        $("#errors span.message-prefix").text("Error: ");
                        $("#errors span.message").text(response.message);
                        $("#errors").show();
                    }
                }
            });
            return false; // Don't scroll to top
        });
        $("a.delete").click(function() {
            $.ajax({
                type: "DELETE",
                url: "statsreports/" + $(this).closest('tr').attr('id'),
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                success: function(response) {
                    $("#errors span.message").text(response.message);
                    if (response.success) {
                        $("#errors").removeClass("alert-danger");
                        $("#errors").addClass("alert-success");
                        $("#errors span.message-prefix").text("Success! ");
                    } else {
                        $("#errors").removeClass("alert-success");
                        $("#errors").addClass("alert-danger");
                        $("#errors span.message-prefix").text("Error: ");
                    }
                    $("#errors").show();

                    if (response.success) {
                        $("#" + response.statsReport).removeClass("success");
                        $("#" + response.statsReport).addClass("danger");

                        $("#" + response.statsReport).each(function() {
                            $('td:nth-child(3)', this).html('<span class="glyphicon glyphicon-remove"></span>')
                                .next().text('-') // Rating
                                .next().text('-') // Submitted At
                                .next().text('-') // Submitted By
                                .next().text('')  // Locked
                                .next().text('')  // View
                                .next().text('')  // Download
                                .next().text(''); // Delete
                        });
                    }
                }
            });
        });
    });
</script>
@endsection

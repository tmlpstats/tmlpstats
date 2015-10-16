@extends('template')

@section('content')

<h2>Global Report - {{ $globalReport->reportingDate->format('F j, Y') }}</h2>
<a href="{{ url('/globalreports') }}"><< See All</a><br/><br/>

<div class="table-responsive">

    <div id="errors" class="alert alert-danger" role="alert" style="display:none">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <span class="message-prefix" style="font-weight:bold">Error: </span>
        <span class="message"></span>
    </div>

    <table class="table table-condensed table-striped">
        <tr>
            <th>Reporting Date:</th>
            <td>{{ $globalReport->reportingDate->format('F j, Y') }}</td>
        </tr>
        {{--<tr>
            <th>Quarter:</th>
            <td>{{ $globalReport->quarter->distinction }} - {{ $globalReport->quarter->startWeekendDate->format('F Y') }}</td>
        </tr>--}}
        <tr>
            <th>Locked:</th>
            <td>
                <a href="#" class="lock" title="Lock" style="color: black">
                    <i class="fa {{ $globalReport->locked ? 'fa-lock' : 'fa-unlock' }}"></i>
                </a>
            </td>
        </tr>
        <tr>
            <th>Stats Reports:</th>
            <td>
                {!! Form::open(['url' => '/globalreports/' . $globalReport->id, 'method' => 'patch', 'class' => 'form-horizontal']) !!}
                    <div class="col-sm-3">
                        {!! Form::select('center', $centers, null, ['class' => 'form-control',  'onchange' => 'this.form.submit()']) !!}
                    </div>
                {!! Form::close() !!}
                <br/>
                <br/>
                <table id="activeCenterTable" class="table table-hover">
                    <thead>
                    <tr>
                        <th>Center</th>
                        <th>Global Region</th>
                        <th>Local Region</th>
                        <th>Reporting Date</th>
                        <th>Rating</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($globalReport->statsReports as $statsReport)
                        <tr id="{{ $statsReport->id }}" >
                            <td>{{ $statsReport->center->name }}</td>
                            <td><?php
                                $region = $statsReport->center->getGlobalRegion();
                                echo ($region) ? $region->name : '-'
                            ?></td>
                            <td><?php
                                $region = $statsReport->center->getLocalRegion();
                                echo ($region) ? $region->name : '-'
                            ?></td>
                            <td>{{ $statsReport->reportingDate->format('F j, Y') }}</td>
                            <td>
                                @if ($statsReport)
                                    {{ $statsReport->getRating() }} ({{ $statsReport->getPoints() }})
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: center">
                                <a href="#" class="remove" title="Remove" style="color: black; hover: ">
                                    <span class="glyphicon glyphicon-remove-circle"></span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $("a.lock").click(function() {
            $.ajax({
                type: "PATCH",
                data: "dataType=JSON&locked=" + $(this).find("i.fa").hasClass("fa-unlock"), // if it is currently unlocked, lock it,
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                success: function(response) {
                    if (response.success) {
                        $("#errors").hide();

                        if (response.locked) {
                            $("a.lock i.fa").removeClass("fa-unlock");
                            $("a.lock i.fa").addClass("fa-lock");
                        } else {
                            $("a.lock i.fa").removeClass("fa-lock");
                            $("a.lock i.fa").addClass("fa-unlock");
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
        $("a.remove").click(function() {
            $.ajax({
                type: "PATCH",
                data: "dataType=JSON&remove=statsreport&id=" + $(this).closest('tr').attr('id'),
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
                        $("#" + response.statsReport).remove();
                    }
                }
            });
        });
    });
</script>
@endsection

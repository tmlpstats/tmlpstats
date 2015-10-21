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
        <tr>
            <th>Stats Reports:</th>
            <td>
                <table id="activeCenterTable" class="table table-hover">
                    <thead>
                    <tr>
                        <th>Center</th>
                        <th>Region</th>
                        <th>Rating</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($globalReport->statsReports as $statsReport)
                        <tr id="{{ $statsReport->id }}" >
                            <td><a href="{{ url("/statsreports/{$statsReport->id}") }}">{{ $statsReport->center->name }}</a></td>
                            <td>{{ $statsReport->center->region->name }}</td>
                            <td>
                                @if ($statsReport)
                                    {{ $statsReport->getRating() }} ({{ $statsReport->getPoints() }})
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>

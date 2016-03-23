<script type="text/javascript">
    var settings = {
        session: {
            viewCenterId: "{{ Session::get('viewCenterId') }}",
            viewRegionId: "{{ Session::get('viewRegionId') }}",
            viewReportingDate: "{{ Session::get('viewReportingDate') }}",
        },

        @if (Auth::user())
        user: {
            id: "{{ Auth::user()->id }}",
            firstName: "{{ Auth::user()->firstName }}",
            lastName: "{{ Auth::user()->lastName }}",
            email: "{{ Auth::user()->email }}",
        },
        @endif

        @if ($center)
        center: {
            id: "{{ $center->id }}",
            name: "{{ $center->name }}",
            abbreviation: "{{ $center->abbreviation }}",
            timezone: "{{ $center->timezone }}",
        },
        @endif

        @if ($region)
        region: {
            id: "{{ $region->id }}",
            name: "{{ $region->name }}",
            abbreviation: "{{ $region->abbreviation }}",
        },
        @endif

        @if ($reportingDate)
        reportingDate: "{{ $reportingDate->toDateString() }}",
        @endif

        LiveScoreboard: {
            editable: {{ (isset($editableLiveScoreboard) && $editableLiveScoreboard) ? 'true' : 'false' }},
        },
    };
</script>

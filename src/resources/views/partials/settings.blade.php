@inject('context', 'TmlpStats\Api\Context')
<?php
$currentUser = Auth::user();
$center = $context->getCenter(true);
$region = $context->getRegion(true);
?>
<script type="text/javascript">
    var settings = {
        session: {
            viewCenterId: @json(Session::get('viewCenterId')),
            viewRegionId: @json(Session::get('viewRegionId')),
            viewReportingDate: @json(Session::get('viewReportingDate')),
        },

        @if ($currentUser)
        user: {
            id: @json($currentUser->id),
            firstName: @json($currentUser->firstName),
            lastName: @json($currentUser->lastName),
            email: @json($currentUser->email),
        },
        @endif

        @if ($center)
        center: @json(array_only($center->toArray(), ['id', 'name', 'abbreviation', 'timezone'])),
        @endif

        @if ($region)
        region: @json(array_only($region->toArray(), ['id', 'name', 'abbreviation'])),
        @endif

        @if ($reportingDate)
        reportingDate: @json($reportingDate->toDateString()),
        @endif

        LiveScoreboard: {
            editable: @json(($currentUser != null) && $context->getSetting('editableLiveScoreboard'))
        },
    };
</script>

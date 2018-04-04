<div class="table-responsive">
    <table class="table table-condensed table-striped">
        <tr>
            <th>Region:</th>
            <td>
                @if ($statsReport->center->getLocalRegion())
                    <?php
                    $region = $statsReport->center->getLocalRegion();
                    if ($region) {
                        echo $region->name;
                    }
                    ?>
                @else
                    <?php
                    $region = $statsReport->center->getGlobalRegion();
                    if ($region) {
                        echo $region->name;
                    }
                    ?>
                @endif
            </td>
        </tr>
        <tr>
            <th>Stats Email:</th>
            <td>{{ $statsReport->center->statsEmail }}</td>
        </tr>
        <tr>
            <th>Submitted At:</th>
            <td><?php
                if ($statsReport->submittedAt) {
                    $submittedAt = clone $statsReport->submittedAt;
                    $submittedAt->setTimezone($statsReport->center->timezone);
                    echo $submittedAt->format('l, F jS \a\t g:ia T');
                } else {
                    echo '-';
                }
                ?></td>
        </tr>
        <tr>
            <th>Rating:</th>
            <td>
                @if ($statsReport && $statsReport->getPoints() !== null)
                    {{ $statsReport->getRating() }} ({{ $statsReport->getPoints() }}pts)
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>Submission Comment:</th>
            <td>{!! nl2br(e($statsReport->submitComment)) !!}</td>
        </tr>
    </table>
</div>

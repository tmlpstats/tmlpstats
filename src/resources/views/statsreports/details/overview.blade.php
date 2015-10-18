<div class="table-responsive">
    <table class="table table-condensed table-striped">
        <tr>
            <th>Center:</th>
            <td>{{ $statsReport->center->name }}</td>
        </tr>
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
            <th>Submitted Sheet Version:</th>
            <td>{{ $statsReport->version }}</td>
        </tr>
        <tr>
            <th>Rating:</th>
            <td>
                @if ($statsReport && $statsReport->getPoints() !== null)
                    {{ $statsReport->getRating() }} ({{ $statsReport->getPoints() }})
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>File:</th>
            <td>
                @if ($sheetUrl)
                    <a href="{{ $sheetUrl }}">Download</a>
                @else
                    <span style="font-style: italic">Sheet not available</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Submission Comment:</th>
            <td>{{ $statsReport->submitComment }}</td>
        </tr>
    </table>
</div>

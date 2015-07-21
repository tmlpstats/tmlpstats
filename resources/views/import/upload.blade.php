<div id="results">

<h2>Imported <?= count($results['sheets']) ?> Sheets</h2>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Decoding the Results:</h3>
    </div>
    <div class="panel-body">
        <ul>
            <li class='ok'>Green: No errors found. Finish by reviewing manually.</li>
            <li class='warning'>Orange: Possible error found. Review items manually.</li>
            <li class='error'>Red: Error found that requires revision. Update and re-run.</li>
        </ul>
    </div>
</div>



<ul>
@foreach ($results['sheets'] as $sheet)
<?php $center = $sheet['center'] ?: 'Unknown'; ?>
<?php $reportingDate = $sheet['reportingDate'] ? $sheet['reportingDate']->format('M j, Y') : 'Unknown'; ?>
<?php $sheetVersion = $sheet['sheetVersion'] ?: 'Unknown'; ?>
    <li class="<?= $sheet['result'] ?>">
        <?= $center.": ".$reportingDate." - sheet v".$sheetVersion ?>
        @if ($sheet['result'] != 'ok')
            <?php
            $messages = array();
            foreach ($sheet['errors'] as $msg) {
                if (isset($msg['offset'])) {
                    $messages[$msg['section']][$msg['offset']]['offsetType'] = $msg['offsetType']; // ugly, but works
                    $messages[$msg['section']][$msg['offset']][] = $msg;
                } else {
                    $messages[$msg['section']][0][] = $msg;
                }
            }
            foreach ($sheet['warnings'] as $msg) {
                if (isset($msg['offset'])) {
                    $messages[$msg['section']][$msg['offset']]['offsetType'] = $msg['offsetType']; // ugly, but works
                    $messages[$msg['section']][$msg['offset']][] = $msg;
                } else {
                    $messages[$msg['section']][0][] = $msg;
                }
            }
            // Presort by row
            foreach ($messages as $section => &$data) {
                if (count($data) <= 1) continue;

                ksort($data);
            }
            ?>
            <ul>
                @foreach ($messages as $tabName => $tab)
                    <li>{{ ucfirst($tabName) }}
                        <ul>
                        @foreach ($tab as $offset => $offsetMessages)
                            @if ($offset === 0)
                                @foreach ($offsetMessages as $msg)
                                    @if (is_array($msg))
                                        <li class="{{ $msg['type'] }}">{{ $msg['message'] }}</li>
                                    @endif
                                @endforeach
                            @else
                            <li>{{ ucfirst($offsetMessages['offsetType']) . " " . $offset }}
                                <ul>
                                @foreach ($offsetMessages as $msg)
                                    @if (is_array($msg))
                                        <li class="{{ $msg['type'] }}">{{ $msg['message'] }}</li>
                                    @endif
                                @endforeach
                                </ul>
                            </li>
                            @endif
                        @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        @endif
    </li>
@endforeach

@foreach ($results['unknownFiles'] as $file)
    <li>{{ $file }}</li>
@endforeach
</ul>

</div>

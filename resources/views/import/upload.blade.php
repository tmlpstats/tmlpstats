<div id="results">

<h2>Imported <?= count($results['sheets']) ?> Sheets</h2>

<ul>
@foreach ($results['sheets'] as $sheet)
    <li class="<?= $sheet['result'] ?>">
        <?= $sheet['center'].": ".$sheet['reportingDate']." - sheet v".$sheet['sheetVersion'] ?>
        @if ($sheet['result'] != 'ok')
            <?php
            $messages = array();
            foreach($sheet['errors'] as $msg) {
                if (array_key_exists('offset', $msg)) {
                    $messages[$msg['section']][$msg['offset']][] = $msg;
                } else {
                    $messages[$msg['section']]['_general'][] = $msg;
                }
            }
            foreach($sheet['warnings'] as $msg) {
                if (array_key_exists('offset', $msg)) {
                    $messages[$msg['section']][$msg['offset']][] = $msg;
                } else {
                    $messages[$msg['section']]['_general'][] = $msg;
                }
            }
            // Presort by row
            foreach ($messages as $section => &$data) {
                if (count($data) <= 1) continue;

                krsort($data);
            }
            ?>
            <ul>
                @foreach ($messages as $tabName => $tab)
                    <li>{{ ucfirst($tabName) }}
                        <ul>
                        @foreach ($tab as $offset => $offsetMessages)
                            @if ($offset == '_general')
                                @foreach($offsetMessages as $msg)
                                    <li class="{{ $msg['type'] }}">{{ $msg['message'] }}</li>
                                @endforeach
                            @else
                            <li>{{ ucfirst($offset) }}
                                <ul>
                                @foreach($offsetMessages as $msg)
                                    <li class="{{ $msg['type'] }}">{{ $msg['message'] }}</li>
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

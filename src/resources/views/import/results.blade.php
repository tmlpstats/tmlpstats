@if ($includeUl)
<ul>
@endif
    <?php
        $center = $sheet['center'] ?: 'Unknown';
        $reportingDate = $sheet['reportingDate'] ? $sheet['reportingDate']->format('M j, Y') : 'Unknown';
        $sheetVersion = $sheet['sheetVersion'] ?: 'Unknown';

        if (!function_exists('getStyleFromClass')) {
            function getStyleFromClass($class) {
                switch ($class) {
                    case 'error':
                        return 'color: red;';
                    case 'warning':
                        return 'color: orange;';
                    case 'ok':
                        return 'color: green;';
                }
            }
        }
    ?>
    <li style="<?= getStyleFromClass($sheet['result']) ?>">
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
                                        <li style="{!! getStyleFromClass($msg['type']) !!}">{{ $msg['message'] }}</li>
                                    @endif
                                @endforeach
                            @else
                            <li>{{ ucfirst($offsetMessages['offsetType']) . " " . $offset }}
                                <ul>
                                @foreach ($offsetMessages as $msg)
                                    @if (is_array($msg))
                                        <li style="{!! getStyleFromClass($msg['type']) !!}">{{ $msg['message'] }}</li>
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
@if ($includeUl)
</ul>
@endif

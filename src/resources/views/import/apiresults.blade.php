<?php
    $title = "{$centerName}: {$reportingDate->format('M j, Y')}";
?>
@if (!$reportMessages)
<ul>
    <li class="ok">{{ $title }}</li>
<ul>
@else
<ul>
    <li class="warning">{{ $title }}
        <ul>
            @foreach ($reportMessages as $group => $groupData)
                <li>{{ ucfirst($group) }}
                    <ul>
                    @foreach ($groupData as $display => $messages)
                        @if ($display)
                            <li>{{ $display }}
                                <ul>
                                @foreach ($messages as $msg)
                                    <li>{{ $msg['message'] }}</li>
                                @endforeach
                                </ul>
                            </li>
                        @else
                            @foreach ($messages as $msg)
                                <li>{{ $msg['message'] }}</li>
                            @endforeach
                        @endif
                    @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </li>
</ul>
@endif

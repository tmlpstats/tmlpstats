<?php
    $title = "{$centerName}: {$reportingDate->format('M j, Y')}";

    // We use inline CSS below because this can also be embedded in emails
?>
@if (!$reportMessages)
<ul>
    <li style="color: green">{{ $title }}</li>
<ul>
@else
<ul>
    <li style="color: orange">{{ $title }}
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

<?php
$divId = isset($divId) ? $divId : 'rating-container';

$display = (isset($statsReport) || (isset($globalReport) && isset($region)));
?>
@if ($display)
<div id="{{ $divId }}" style="min-width: 90%; height: 400px; margin: 0 auto"></div>
@else
<p>No Ratings summary available</p>
@endif

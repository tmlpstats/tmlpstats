<h3>Report Details</h3>
@include('statsreports.details.overview', ['statsReport' => $statsReport, 'sheetUrl' => $sheetUrl])
<h3>Results</h3>
<div id="results-container">{!! $results !!}</div>

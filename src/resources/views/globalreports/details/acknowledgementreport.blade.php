<div class="table-responsive acknowledgementReport">
    <br/>
    <h3>Acknowledgement Report</h3>
    <h4>February 2017</h4>
    <br/>

    @foreach ($reportData['regions'] as $regionName => $regionData)
    <h4>{{ $regionName }} - {{ $regionData['effectiveness'] }}</h4>
    <table class="ratingTable">
        @foreach ($regionData['centers'] as $rating => $list)
        <?php sort($list); ?>
        <tr>
            <td>{{ $rating }}</td>
            <td>{{ count($list) }}</td>
            <td>{{ implode(', ', $list) }}</td>
        </tr>
        @endforeach
    </table>
    @endforeach
    <br/>
    @foreach ($reportData['100pctGames'] as $game => $list)
        <?php sort($list); ?>
        @if ($game == '4+')
            <h4>Centers with four or more games 100%+ ({{ count($list) }})</h4>
            <p>{{ $list ? implode(', ', $list) : '&nbsp;' }}</p>
        @else
            <h4>{{ strtoupper($game) }} 100%+ ({{ count($list) }})</h4>
            <p>{{ $list ? implode(', ', $list) : '&nbsp;' }}</p>
        @endif
    @endforeach
</div>

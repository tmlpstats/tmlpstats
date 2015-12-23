<div class="boxes">
    @foreach ($boxes as $box)
    <div class="box">
        <div>
            <span class="stat">
                {{ $box['stat'] }}{!! isset($box['subStat']) ? ('<span class="subStat">/' . $box['subStat'] . '</span>') : '' !!}
            </span>
        </div>
        <div>
            <span class="description">{{ $box['description'] }}</span>
        </div>
    </div>
    @endforeach
    <div class="after-box"></div>
</div>

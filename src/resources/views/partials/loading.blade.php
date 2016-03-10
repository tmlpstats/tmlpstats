<div class="loader" {!! isset($id) ? "id='{$id}'" : '' !!} {!! (isset($show) && $show == false) ? "style='display:none'" : '' !!}>
    @include('partials.loaders.rainbowswirl')
</div>

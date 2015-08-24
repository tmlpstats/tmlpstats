<div id="results">

@if (isset($results['messages']))
    <br/>
    @if (isset($results['messages']['success']))
        @foreach ($results['messages']['success'] as $message)
        <div class="alert alert-success" role="alert">{!! $message !!}</div>
        @endforeach
    @endif

    @if (isset($results['messages']['error']))
        @foreach ($results['messages']['error'] as $message)
        <div class="alert alert-danger" role="alert">{!! $message !!}</div>
        @endforeach
    @endif
@endif

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
    @include('import.results', ['sheet' => $sheet, 'includeUl' => false])
@endforeach

@foreach ($results['unknownFiles'] as $file)
    <li>{{ $file }}</li>
@endforeach
</ul>

</div>

@if (Session::has('results'))
    @foreach (Session::get('results') as $type => $results)
        @if ($results)
            <?php $class = ($type == 'error') ? 'alert-danger' : 'alert-success'; ?>
            <br>
            <div class="alert {{ $class }}" role="alert">
                <ul>
                    @foreach($results as $result)
                        {!! $result !!}
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach
@endif

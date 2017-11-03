@extends('template')

@section('content')
    <h1>Help Videos</h1>

    <p>
        Please select a video to watch.
    </p>
    
    <ol>
    @foreach ($videos as $video)
        <li>
            @if (!empty($video->tag))
                <span class="label label-info">{{ $video->tag }}</span>
            @endif
            <a href="{{ action('HelpController@view', ['file' => $video->file]) }}">
                {{ $video->title }}
            </a>
        </li>
    @endforeach
    </ol>

@endsection

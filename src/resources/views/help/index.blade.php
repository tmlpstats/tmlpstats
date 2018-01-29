@extends('template')

@section('content')
    <h1>Help Videos</h1>
    @if (count($videos) == 0)
        <p>No videos available at this time.</p>
    @else
        <p>Please select a video to watch.</p>

        <ol>
        @foreach ($videos as $video)
            <li>
                @foreach ($video['tags'] as $tag)
                    <span class="label label-info">{{ $tag }}</span>
                @endforeach
                <a href="{{ action('HelpController@view', ['id' => $video['id']]) }}">
                    {{ $video['title'] }}
                </a>
                <p>{{ $video['description'] }}</p>
            </li>
        @endforeach
        </ol>
    @endif
@endsection

@extends('template')

@section('content')
    <h1>Help Videos</h1>
    @if (count($videos) == 0)
        <p>No videos available at this time.</p>
    @else
        <p>Here's a selection of videos to show how to use different parts of tmlpstats.com. If you have any special requests for videos you'd like to see, feel free to send us some feedback.</p>
    <div class="container-fluid">
        <div class="col-sm-6">
            <?php
                $fw = $videos->filter(function($video) {
                    return $video['tags']->contains('first week');
                })->sortBy('order')->all();

                $others = $videos->filter(function($video) {
                    return $video['tags']->isEmpty();
                })->sortBy('order')->all();

                $admin = $videos->filter(function($video) {
                    return $video['tags']->contains('admin');
                })->sortBy('order')->all();

                $regional = $videos->filter(function($video) {
                    return $video['tags']->contains('regional');
                })->sortBy('order')->all();
            ?>
            @if ($fw)
                <h3>First Week</h3>
                <ol>
                @foreach ($fw as $video)
                    <li>
                        <a href="{{ action('HelpController@view', ['id' => $video['id']]) }}">
                            {{ $video['title'] }}
                        </a>
                        <p>{{ $video['description'] }}</p>
                    </li>
                @endforeach
                </ol>
            @endif
            @if ($others)
            <h3>Current Stats</h3>
            <ol>
            @foreach ($others as $video)
                @if ($video['tags']->isEmpty())
                <li>
                    <a href="{{ action('HelpController@view', ['id' => $video['id']]) }}">
                        {{ $video['title'] }}
                    </a>
                    <p>{{ $video['description'] }}</p>
                </li>
                @endif
            @endforeach
            </ol>
            @endif
        </div>
        <div class="col-sm-6">
            @if ($admin)
            <h3>Admin</h3>
            <ol>
            @foreach ($admin as $video)
                @if ($video['tags']->contains('admin'))
                <li>
                    <a href="{{ action('HelpController@view', ['id' => $video['id']]) }}">
                        {{ $video['title'] }}
                    </a>
                    <p>{{ $video['description'] }}</p>
                </li>
                @endif
            @endforeach
            </ol>
            @endif
            @if ($regional)
            <h3>Regional</h3>
            <ol>
            @foreach ($regional as $video)
                @if ($video['tags']->contains('regional'))
                <li>
                    <a href="{{ action('HelpController@view', ['id' => $video['id']]) }}">
                        {{ $video['title'] }}
                    </a>
                    <p>{{ $video['description'] }}</p>
                </li>
                @endif
            @endforeach
            </ol>
            @endif
        </div>
    </div>
    @endif
@endsection

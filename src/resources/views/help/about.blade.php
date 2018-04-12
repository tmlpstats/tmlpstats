@extends('template')

@section('content')
    <h1>About</h1>
    <p>
        The TMLP Stats page is developed and maintained by the Future of Stats Team.
    </p>
    <p>
        <u>Future of Stats team is the possibility of being simplicity and ease,
        providing energy and momentum for the transformation of humanity.</u>
    </p>
    <p>
        The following people comprise the Future of Stats Team:
    </p>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Peter A</h2>
            </div>
            <div class="col-md-4">
                <img src="{{ URL::asset('img/peter-a.jpg') }}" alt="Peter profile picture" class="img-responsive">
            </div>
            <div class="col-md-8">
                <p>
                    During the day I manage a team of software engineers. When
                    I’m not at work, I love to travel, learn, and tinker with
                    tech.
                </p>
                <p>
                    After completing Team 2 in 2014, I helped start the Future
                    of Stats team with the goal of transforming the way we work
                    with and think about statistics by getting technology out of
                    the way. In the process I’ve had the opportunity to work
                    with people from around the world, explore new technologies,
                    and try out roles like product management, UX/UI, and
                    customer support.
                </p>
                <p>
                    The real reason I participate, though, is because the Team,
                    Management and Leadership Program had a huge impact on my
                    life, and I love contributing back to the TMLP community.
                </p>
            </div>
        </div>
    </div>
    <hr />

    <div class="container-fluid">
        <div class="row">
            <h2>James C</h2>
            <div class="col-md-4">

            </div>
            <div class="col-md-8">
                <p>

                </p>
            </div>
        </div>
    </div>

    <hr />
    <div class="container">
        <div class="row">
            <h2>Rex L</h2>
            <div class="col-md-4">
                <img src="{{ URL::asset('img/rex-l.jpg') }}" alt="Rex profile picture" class="img-responsive">
            </div>
            <div class="col-md-8">
                <p>
                    In the world I am a web developer at UCLA managing the Learning
                    Management System for all the staff, faculty, and students. I
                    am recently married and excited to start a family.
                </p>
                <p>
                    I completed Team 1 in June 2017 with Team Los Angeles North America
                    and joined the Future of Stats team because I love technology
                    and my experience as Statistician accountable during my 3rd quarter.
                </p>
                <p>
                    If you want breakthroughs in integrity, relatedness, and
                    numbers join the Future of Stats Team! Also if you are interested
                    in learning some cutting edge industry level technologies
                    like Laravel, Bootstrap, Docker, etc join now.
                </p>
            </div>
        </div>
    </div>
    <hr />

    <p>
        If you saw something from our shares, please reach out to us using the
        Feedback form and drop in during our weekly calls as we discuss the
        Future of Stats.
    </p>
@endsection
